<?php

use App\Models\Lesson;
use App\Models\Student;
use App\Models\XpEntry;

it('records xp for a student on a lesson', function () {
    $lesson = Lesson::factory()->create(['max_points' => 300]);
    $student = Student::factory()->create();

    $response = $this->putJson('/xp', [
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'points' => 250,
    ]);

    $response->assertOk();
    $response->assertJson(['saved' => true, 'total_points' => 250]);

    $this->assertDatabaseHas('xp_entries', [
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'points' => 250,
    ]);
});

it('rejects xp above the lesson maximum', function () {
    $lesson = Lesson::factory()->create(['max_points' => 100]);
    $student = Student::factory()->create();

    $response = $this->putJson('/xp', [
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'points' => 150,
    ]);

    $response->assertUnprocessable();
    $this->assertDatabaseMissing('xp_entries', [
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
    ]);
});

it('clears an xp entry when points is set to null', function () {
    $lesson = Lesson::factory()->create(['max_points' => 300]);
    $student = Student::factory()->create();
    XpEntry::factory()->create(['student_id' => $student->id, 'lesson_id' => $lesson->id, 'points' => 100]);

    $response = $this->putJson('/xp', [
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'points' => null,
    ]);

    $response->assertOk();
    $this->assertDatabaseMissing('xp_entries', [
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
    ]);
});

it('fills the same xp for every student on a lesson', function () {
    $lesson = Lesson::factory()->create(['max_points' => 300]);
    $students = Student::factory()->count(3)->create();

    $response = $this->putJson('/xp/fill-all', [
        'lesson_id' => $lesson->id,
        'points' => 275,
    ]);

    $response->assertOk();

    foreach ($students as $student) {
        $this->assertDatabaseHas('xp_entries', [
            'student_id' => $student->id,
            'lesson_id' => $lesson->id,
            'points' => 275,
        ]);
    }
});
