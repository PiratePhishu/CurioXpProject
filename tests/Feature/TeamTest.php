<?php

use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use App\Models\XpEntry;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

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

it('links each team row to its detail page', function () {
    Student::factory()->create(['team' => 'A']);

    $response = $this->get('/teams');

    $response->assertOk();
    $response->assertSee(route('teams.show', 'A'), false);
});

it('shows only the students in that team and allows adjusting their xp', function () {
    $lesson = Lesson::factory()->create(['max_points' => 300]);
    $teamA = Student::factory()->create(['team' => 'A', 'name' => 'Lid Team A']);
    $teamB = Student::factory()->create(['team' => 'B', 'name' => 'Lid Team B']);

    $response = $this->get(route('teams.show', 'A'));

    $response->assertOk();
    $response->assertSee('Lid Team A');
    $response->assertDontSee('Lid Team B');

    $xpResponse = $this->putJson('/xp', [
        'student_id' => $teamA->id,
        'lesson_id' => $lesson->id,
        'points' => 180,
    ]);

    $xpResponse->assertOk();
    $this->assertDatabaseHas('xp_entries', [
        'student_id' => $teamA->id,
        'lesson_id' => $lesson->id,
        'points' => 180,
    ]);

    expect($teamB)->not->toBeNull();
});

it('returns a 404 for a team with no students', function () {
    $this->get(route('teams.show', 'Z'))->assertNotFound();
});
