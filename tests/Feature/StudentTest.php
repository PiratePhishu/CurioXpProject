<?php

use App\Models\Student;
use App\Models\User;

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

it('deletes a student and their xp entries', function () {
    $student = Student::factory()->create();

    $response = $this->delete("/studenten/{$student->id}");

    $response->assertRedirect(route('students.index'));
    $this->assertDatabaseMissing('students', ['id' => $student->id]);
});
