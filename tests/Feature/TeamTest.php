<?php

use App\Models\Lesson;
use App\Models\Student;
use App\Models\XpEntry;

it('aggregates xp per team and ranks teams by total', function () {
    $lesson = Lesson::factory()->create(['max_points' => 1000]);

    $teamA1 = Student::factory()->create(['team' => 'A']);
    $teamA2 = Student::factory()->create(['team' => 'A']);
    $teamB1 = Student::factory()->create(['team' => 'B']);

    XpEntry::factory()->create(['student_id' => $teamA1->id, 'lesson_id' => $lesson->id, 'points' => 100]);
    XpEntry::factory()->create(['student_id' => $teamA2->id, 'lesson_id' => $lesson->id, 'points' => 200]);
    XpEntry::factory()->create(['student_id' => $teamB1->id, 'lesson_id' => $lesson->id, 'points' => 500]);

    $response = $this->get('/teams');

    $response->assertOk();
    $response->assertSeeInOrder(['B', 'A']);
    $response->assertSee('300');
    $response->assertSee('500');
});

it('excludes students without a team from the ranking', function () {
    Student::factory()->create(['team' => null]);

    $response = $this->get('/teams');

    $response->assertOk();
    $response->assertSee('Nog geen teams ingesteld.');
});
