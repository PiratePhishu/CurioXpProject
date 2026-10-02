<?php

use App\Models\Student;

it('adds a new student', function () {
    $response = $this->post('/studenten', [
        'name' => 'Nieuwe Student',
        'team' => 'C',
    ]);

    $response->assertRedirect(route('students.index'));
    $this->assertDatabaseHas('students', [
        'name' => 'Nieuwe Student',
        'team' => 'C',
    ]);
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
        'name' => 'Nieuwe Naam',
        'team' => 'B',
    ]);
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
