<?php

use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->teacher = User::factory()->create();
    $this->actingAs($this->teacher);
});

it('redirects to the class management page when the teacher has no class selected', function () {
    $teacher = User::factory()->create(['current_school_class_id' => null]);
    $this->actingAs($teacher);

    $this->get(route('scoreboard'))->assertRedirect(route('classes.index'));
});

it('allows renaming a class', function () {
    $class = SchoolClass::factory()->create(['name' => 'Oude Naam']);

    $response = $this->patchJson("/klassen/{$class->id}", ['name' => 'Nieuwe Naam']);

    $response->assertOk();
    expect($class->fresh()->name)->toBe('Nieuwe Naam');
});

it('defaults the new class form to the current academic year', function () {
    $response = $this->get(route('classes.index'));

    $response->assertOk();
    $response->assertSee(SchoolYear::currentAcademicYearLabel());
});

it('creates a new class in the chosen school year that copies lesson names but starts without students', function () {
    $currentClass = $this->teacher->currentSchoolClass;
    Lesson::factory()->create(['school_class_id' => $currentClass->id, 'code' => 'L1', 'name' => 'Intro']);
    Student::factory()->create(['school_class_id' => $currentClass->id]);

    $response = $this->post(route('classes.store'), [
        'name' => 'Nieuwe Klas',
        'year' => $currentClass->schoolYear->name,
    ]);

    $response->assertRedirect(route('classes.index'));

    $newClass = SchoolClass::query()->where('name', 'Nieuwe Klas')->firstOrFail();

    expect($newClass->school_year_id)->toBe($currentClass->school_year_id);
    expect($newClass->lessons()->pluck('name'))->toContain('Intro');
    expect($newClass->students()->count())->toBe(0);
});

it('creates a new school year on the fly when none matches', function () {
    $response = $this->post(route('classes.store'), [
        'name' => 'Nieuwe Klas',
        'year' => '2099/2100',
    ]);

    $response->assertRedirect(route('classes.index'));

    $schoolYear = SchoolYear::query()->where('name', '2099/2100')->first();
    expect($schoolYear)->not->toBeNull();

    $newClass = SchoolClass::query()->where('name', 'Nieuwe Klas')->firstOrFail();
    expect($newClass->school_year_id)->toBe($schoolYear->id);
});

it('rejects a duplicate class name within the same school year', function () {
    $schoolYear = $this->teacher->currentSchoolClass->schoolYear;
    SchoolClass::factory()->create(['school_year_id' => $schoolYear->id, 'name' => 'Bestaande Klas']);

    $response = $this->post(route('classes.store'), [
        'name' => 'Bestaande Klas',
        'year' => $schoolYear->name,
    ]);

    $response->assertSessionHasErrors('name');
});

it('allows the same class name in a different school year', function () {
    $schoolYear = $this->teacher->currentSchoolClass->schoolYear;
    SchoolClass::factory()->create(['school_year_id' => $schoolYear->id, 'name' => 'Klas 1']);

    $response = $this->post(route('classes.store'), [
        'name' => 'Klas 1',
        'year' => '2099/2100',
    ]);

    $response->assertRedirect(route('classes.index'));
    expect(SchoolClass::query()->where('name', 'Klas 1')->count())->toBe(2);
});

it('groups the class switcher options by school year', function () {
    $otherYear = SchoolYear::factory()->create(['name' => '2099/2100']);
    SchoolClass::factory()->create(['school_year_id' => $otherYear->id, 'name' => 'Toekomstklas']);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('<optgroup label="2099/2100">', false);
    $response->assertSee('Toekomstklas');
});

it('only shows the active class\'s students on the scoreboard after switching', function () {
    $classA = $this->teacher->currentSchoolClass;
    Student::factory()->create(['school_class_id' => $classA->id, 'name' => 'Student Klas A']);

    $classB = SchoolClass::factory()->create(['name' => 'Klas B']);
    Student::factory()->create(['school_class_id' => $classB->id, 'name' => 'Student Klas B']);

    $this->get('/')->assertSee('Student Klas A')->assertDontSee('Student Klas B');

    $this->post(route('classes.activate'), ['school_class_id' => $classB->id]);

    $this->get('/')->assertSee('Student Klas B')->assertDontSee('Student Klas A');
});
