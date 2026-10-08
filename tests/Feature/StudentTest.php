<?php

use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use App\Models\XpEntry;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('adds a new student', function () {
    $response = $this->post('/studenten', [
        'name' => 'Nieuwe Student',
        'team' => 'C',
    ]);

    $response->assertRedirect(route('students.index'));
    $this->assertDatabaseHas('students', ['team' => 'C']);
    expect(Student::query()->where('team', 'C')->first()->name)->toBe('Nieuwe Student');
});

it('updates a student name and team', function () {
    $student = Student::factory()->create(['name' => 'Oude Naam', 'team' => 'A']);

    $response = $this->patchJson("/studenten/{$student->id}", [
        'name' => 'Nieuwe Naam',
        'team' => 'B',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'team' => 'B',
    ]);
    expect($student->fresh()->name)->toBe('Nieuwe Naam');
});

it('clears the team label when set to an empty string', function () {
    $student = Student::factory()->create(['team' => 'A']);

    $this->patchJson("/studenten/{$student->id}", ['team' => ''])->assertOk();

    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'team' => null,
    ]);
});

it('sorts students by name numerically instead of alphabetically', function () {
    Student::factory()->create(['name' => 'Student 2']);
    Student::factory()->create(['name' => 'Student 10']);
    Student::factory()->create(['name' => 'Student 1']);

    $response = $this->get('/studenten?sort=naam&dir=asc');

    $response->assertOk();
    $response->assertSeeInOrder(['Student 1', 'Student 2', 'Student 10']);
});

it('soft-deletes a student, hiding them without destroying their xp history', function () {
    $lesson = Lesson::factory()->create();
    $student = Student::factory()->create(['name' => 'Verwijderde Student']);
    $xpEntry = XpEntry::factory()->create(['student_id' => $student->id, 'lesson_id' => $lesson->id, 'points' => 50]);

    $response = $this->delete("/studenten/{$student->id}");

    $response->assertRedirect(route('students.index'));

    expect(Student::query()->find($student->id))->toBeNull();
    expect(Student::withTrashed()->find($student->id))->not->toBeNull();
    $this->assertDatabaseHas('xp_entries', ['id' => $xpEntry->id]);

    $this->get(route('students.index'))->assertDontSee('Verwijderde Student');
});
