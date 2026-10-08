<?php

use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Models\XpEntry;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->teacher = User::factory()->create();
    $this->actingAs($this->teacher);
});

it('downloads an import template with the expected headings', function () {
    $response = $this->get(route('students.import.template'));

    $response->assertOk();
    $response->assertHeader(
        'content-type',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );
});

it('imports new students from a csv file, deriving email and reformatting the name', function () {
    $file = UploadedFile::fake()->createWithContent(
        'studenten.csv',
        "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n303116,\"El Idrissi, Bilal\"\n"
    );

    $response = $this->post(route('students.import'), ['file' => $file]);

    $response->assertRedirect(route('students.index'));

    $names = Student::query()->get()->pluck('name');
    expect($names)->toContain('Anne Jansen');
    expect($names)->toContain('Bilal El Idrissi');

    $anne = Student::query()->get()->sole(fn (Student $student) => $student->email === 'd303115@edu.curio.nl');
    expect($anne->name)->toBe('Anne Jansen');
    expect($anne->must_change_password)->toBeTrue();
});

it('keeps a name without a comma as-is', function () {
    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Studentnummer,Naam\n303117,Madonna\n");

    $this->post(route('students.import'), ['file' => $file]);

    $student = Student::query()->get()->sole(fn (Student $student) => $student->email === 'd303117@edu.curio.nl');
    expect($student->name)->toBe('Madonna');
});

it('updates an existing student matched by the derived email instead of creating a duplicate', function () {
    $existing = Student::factory()->create(['name' => 'Oude Naam', 'email' => 'd303115@edu.curio.nl']);

    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n");

    $this->post(route('students.import'), ['file' => $file]);

    expect(Student::query()->count())->toBe(1);
    expect($existing->fresh()->name)->toBe('Anne Jansen');
    expect($existing->fresh()->email)->toBe('d303115@edu.curio.nl');
});

it('restores an accidentally deleted student and their xp history on re-import', function () {
    $lesson = Lesson::factory()->create();
    $student = Student::factory()->create(['name' => 'Oude Naam', 'email' => 'd303115@edu.curio.nl']);
    $xpEntry = XpEntry::factory()->create(['student_id' => $student->id, 'lesson_id' => $lesson->id, 'points' => 75]);

    $this->delete(route('students.destroy', $student));
    expect(Student::query()->find($student->id))->toBeNull();

    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n");

    $response = $this->post(route('students.import'), ['file' => $file]);
    $response->assertRedirect(route('students.index'));

    expect(Student::query()->count())->toBe(1);

    $restored = Student::query()->find($student->id);
    expect($restored)->not->toBeNull();
    expect($restored->name)->toBe('Anne Jansen');
    expect($restored->xpEntries()->where('id', $xpEntry->id)->exists())->toBeTrue();
});

it('skips rows with a missing name and ignores header and blank rows', function () {
    $file = UploadedFile::fake()->createWithContent(
        'studenten.csv',
        "Studentnummer,Naam\n303118,\n,\n"
    );

    $this->post(route('students.import'), ['file' => $file]);

    expect(Student::query()->count())->toBe(0);
});

it('does not match a student with the same derived email from a different class', function () {
    $otherClass = SchoolClass::factory()->create();
    Student::factory()->create(['school_class_id' => $otherClass->id, 'email' => 'd303115@edu.curio.nl', 'name' => 'Andere Klas']);

    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n");

    $this->post(route('students.import'), ['file' => $file]);

    $ownStudents = Student::query()->where('school_class_id', $this->teacher->current_school_class_id)->get();
    expect($ownStudents)->toHaveCount(1);
    expect($ownStudents->first()->name)->toBe('Anne Jansen');
});
