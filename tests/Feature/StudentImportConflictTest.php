<?php

use App\Models\Lesson;
use App\Models\PendingStudentImport;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Models\XpEntry;

beforeEach(function () {
    $this->teacher = User::factory()->create();
    $this->actingAs($this->teacher);

    $this->targetClassId = $this->teacher->current_school_class_id;
    $this->otherClass = SchoolClass::factory()->create(['name' => 'Oude Klas']);
});

function classConflictFor(Student $existing, array $names = ['Anne Jansen']): PendingStudentImport
{
    return PendingStudentImport::factory()->create([
        'school_class_id' => test()->targetClassId,
        'existing_student_id' => $existing->id,
        'student_number' => '303115',
        'candidate_names' => $names,
    ]);
}

it('shows the open conflicts with the class the student is in now and their xp', function () {
    $existing = Student::factory()->create(['school_class_id' => $this->otherClass->id, 'name' => 'Anne Jansen']);
    XpEntry::factory()->create([
        'student_id' => $existing->id,
        'lesson_id' => Lesson::factory()->create(['school_class_id' => $this->otherClass->id])->id,
        'points' => 120,
    ]);
    classConflictFor($existing);

    $response = $this->get(route('students.import.conflicts'));

    $response->assertOk();
    $response->assertSee('Studentnummer 303115');
    $response->assertSee('Laten staan in Oude Klas');
    $response->assertSee('heeft 120 XP in Oude Klas; dit wordt verwijderd.');
});

it('redirects to the students page when there are no open conflicts', function () {
    $this->get(route('students.import.conflicts'))->assertRedirect(route('students.index'));
});

it('shows a banner on the students page while conflicts are open', function () {
    PendingStudentImport::factory()->count(2)->create(['school_class_id' => $this->targetClassId]);

    $this->get(route('students.index'))->assertSee('Er zijn 2 importconflicten open');
});

it('moves a student into this class, deleting their old xp and resetting team and position', function () {
    Student::factory()->create(['school_class_id' => $this->targetClassId, 'position' => 7]);
    $existing = Student::factory()->create(['school_class_id' => $this->otherClass->id, 'name' => 'Oude Naam', 'team' => 'B']);
    XpEntry::factory()->create([
        'student_id' => $existing->id,
        'lesson_id' => Lesson::factory()->create(['school_class_id' => $this->otherClass->id])->id,
    ]);
    $conflict = classConflictFor($existing);

    $response = $this->post(route('students.import.conflicts.resolve'), [
        'resolutions' => [$conflict->id => ['placement' => 'move']],
    ]);

    $response->assertRedirect(route('students.index'));

    $existing->refresh();
    expect($existing->school_class_id)->toBe($this->targetClassId);
    expect($existing->name)->toBe('Anne Jansen');
    expect($existing->team)->toBeNull();
    expect($existing->position)->toBe(8);
    expect($existing->xpEntries()->count())->toBe(0);
    expect(Student::query()->count())->toBe(2);
    expect(PendingStudentImport::query()->count())->toBe(0);
});

it('restores a soft-deleted student when moving them', function () {
    $existing = Student::factory()->create(['school_class_id' => $this->otherClass->id]);
    $existing->delete();
    $conflict = classConflictFor($existing);

    $this->post(route('students.import.conflicts.resolve'), [
        'resolutions' => [$conflict->id => ['placement' => 'move']],
    ]);

    expect(Student::query()->find($existing->id)?->school_class_id)->toBe($this->targetClassId);
});

it('keeps a student in their current class untouched', function () {
    $existing = Student::factory()->create(['school_class_id' => $this->otherClass->id, 'name' => 'Oude Naam']);
    XpEntry::factory()->create([
        'student_id' => $existing->id,
        'lesson_id' => Lesson::factory()->create(['school_class_id' => $this->otherClass->id])->id,
    ]);
    $conflict = classConflictFor($existing);

    $this->post(route('students.import.conflicts.resolve'), [
        'resolutions' => [$conflict->id => ['placement' => 'keep']],
    ])->assertRedirect(route('students.index'));

    $existing->refresh();
    expect($existing->school_class_id)->toBe($this->otherClass->id);
    expect($existing->name)->toBe('Oude Naam');
    expect($existing->xpEntries()->count())->toBe(1);
    expect(PendingStudentImport::query()->count())->toBe(0);
});

it('creates a new student with the chosen name for an in-file duplicate', function () {
    $conflict = PendingStudentImport::factory()->create([
        'school_class_id' => $this->targetClassId,
        'student_number' => '303115',
        'candidate_names' => ['Anne Jansen', 'Anna Jansen'],
    ]);

    $this->post(route('students.import.conflicts.resolve'), [
        'resolutions' => [$conflict->id => ['name' => 'Anna Jansen']],
    ]);

    $student = Student::query()->sole();
    expect($student->name)->toBe('Anna Jansen');
    expect($student->email)->toBe('d303115@edu.curio.nl');
    expect($student->school_class_id)->toBe($this->targetClassId);
});

it('renames the existing student in this class for an in-file duplicate', function () {
    $existing = Student::factory()->create(['school_class_id' => $this->targetClassId, 'name' => 'Oude Naam']);
    $conflict = classConflictFor($existing, ['Anne Jansen', 'Anna Jansen']);

    $this->post(route('students.import.conflicts.resolve'), [
        'resolutions' => [$conflict->id => ['name' => 'Anna Jansen']],
    ]);

    expect($existing->fresh()->name)->toBe('Anna Jansen');
    expect(Student::query()->count())->toBe(1);
});

it('asks for both the name and the class when a duplicate in the file is also in another class', function () {
    $existing = Student::factory()->create(['school_class_id' => $this->otherClass->id]);
    $conflict = classConflictFor($existing, ['Anne Jansen', 'Anna Jansen']);

    $this->post(route('students.import.conflicts.resolve'), [
        'resolutions' => [$conflict->id => ['placement' => 'move']],
    ])->assertSessionHasErrors("resolutions.{$conflict->id}.name");

    $this->post(route('students.import.conflicts.resolve'), [
        'resolutions' => [$conflict->id => ['placement' => 'move', 'name' => 'Anna Jansen']],
    ])->assertSessionHasNoErrors();

    expect($existing->fresh()->name)->toBe('Anna Jansen');
    expect($existing->fresh()->school_class_id)->toBe($this->targetClassId);
});

it('saves nothing until every conflict has a choice', function () {
    $first = Student::factory()->create(['school_class_id' => $this->otherClass->id]);
    $second = Student::factory()->create(['school_class_id' => $this->otherClass->id]);
    $firstConflict = classConflictFor($first);
    classConflictFor($second);

    $response = $this->post(route('students.import.conflicts.resolve'), [
        'resolutions' => [$firstConflict->id => ['placement' => 'move']],
    ]);

    $response->assertSessionHasErrors();
    expect($first->fresh()->school_class_id)->toBe($this->otherClass->id);
    expect(PendingStudentImport::query()->count())->toBe(2);
});

it('rejects a name that was not in the file', function () {
    $conflict = PendingStudentImport::factory()->create([
        'school_class_id' => $this->targetClassId,
        'candidate_names' => ['Anne Jansen', 'Anna Jansen'],
    ]);

    $this->post(route('students.import.conflicts.resolve'), [
        'resolutions' => [$conflict->id => ['name' => 'Iemand Anders']],
    ])->assertSessionHasErrors("resolutions.{$conflict->id}.name");

    expect(Student::query()->count())->toBe(0);
});

it('only resolves conflicts of the current class', function () {
    $foreignConflict = PendingStudentImport::factory()->create(['school_class_id' => $this->otherClass->id]);

    $this->post(route('students.import.conflicts.resolve'), [
        'resolutions' => [$foreignConflict->id => ['name' => $foreignConflict->candidate_names[0]]],
    ]);

    expect(Student::query()->count())->toBe(0);
    expect(PendingStudentImport::query()->count())->toBe(1);
});

it('discards the open conflicts of the current class without saving any student', function () {
    $existing = Student::factory()->create(['school_class_id' => $this->otherClass->id, 'name' => 'Anne Jansen']);
    classConflictFor($existing);
    PendingStudentImport::factory()->create(['school_class_id' => $this->targetClassId]);
    $foreignConflict = PendingStudentImport::factory()->create(['school_class_id' => $this->otherClass->id]);

    $response = $this->delete(route('students.import.conflicts.destroy'));

    $response->assertRedirect(route('students.index'));
    $response->assertSessionHas('status', '2 importconflict(en) verworpen. Je kunt nu opnieuw importeren.');
    expect(PendingStudentImport::query()->pluck('id')->all())->toBe([$foreignConflict->id]);
    expect(Student::query()->count())->toBe(1);
    expect($existing->fresh()->school_class_id)->toBe($this->otherClass->id);
});

it('shows a button to discard the open conflicts', function () {
    PendingStudentImport::factory()->create(['school_class_id' => $this->targetClassId]);

    $this->get(route('students.import.conflicts'))->assertSee('Conflicten verwerpen');
});

it('prunes conflicts that have been open for more than a week', function () {
    $stale = PendingStudentImport::factory()->create(['created_at' => now()->subDays(8)]);
    $fresh = PendingStudentImport::factory()->create(['created_at' => now()->subDays(6)]);

    $this->artisan('model:prune', ['--model' => [PendingStudentImport::class]])->assertSuccessful();

    expect(PendingStudentImport::query()->pluck('id')->all())->toBe([$fresh->id]);
    expect(PendingStudentImport::query()->find($stale->id))->toBeNull();
});
