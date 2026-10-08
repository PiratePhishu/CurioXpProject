<?php

use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use App\Models\XpEntry;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('ranks students by total xp and shows their level', function () {
    $lesson = Lesson::factory()->create(['max_points' => 1000]);

    $leader = Student::factory()->create(['name' => 'Zoë', 'position' => 2]);
    $runnerUp = Student::factory()->create(['name' => 'Aad', 'position' => 1]);

    XpEntry::factory()->create(['student_id' => $leader->id, 'lesson_id' => $lesson->id, 'points' => 700]);
    XpEntry::factory()->create(['student_id' => $runnerUp->id, 'lesson_id' => $lesson->id, 'points' => 100]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSeeInOrder(['Zoë', 'Aad']);
    $response->assertSee('Apprentice');
    $response->assertSee('Novice');
});

it('treats students without xp entries as zero points', function () {
    Student::factory()->create(['name' => 'Nieuwe Student']);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Nieuwe Student');
    $response->assertSee('Novice');
});

it('sorts students by name numerically instead of alphabetically', function () {
    Student::factory()->create(['name' => 'Student 2']);
    Student::factory()->create(['name' => 'Student 10']);
    Student::factory()->create(['name' => 'Student 1']);

    $response = $this->get('/?sort=naam&dir=asc');

    $response->assertOk();
    $response->assertSeeInOrder(['Student 1', 'Student 2', 'Student 10']);
});
