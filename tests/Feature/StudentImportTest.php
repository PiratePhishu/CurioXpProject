<?php

use App\Models\Lesson;
use App\Models\PendingStudentImport;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
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

it('holds a student who is already in a different class as a conflict instead of saving them twice', function () {
    $otherClass = SchoolClass::factory()->create();
    $existing = Student::factory()->create(['school_class_id' => $otherClass->id, 'email' => 'd303115@edu.curio.nl', 'name' => 'Andere Klas']);

    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n303116,\"El Idrissi, Bilal\"\n");

    $response = $this->post(route('students.import'), ['file' => $file]);

    $response->assertRedirect(route('students.import.conflicts'));
    $response->assertSessionHas('status', fn (string $status) => str_contains($status, '1 dubbel(e) studentnummer(s) gevonden'));

    $ownStudents = Student::query()->where('school_class_id', $this->teacher->current_school_class_id)->get();
    expect($ownStudents->pluck('name')->all())->toBe(['Bilal El Idrissi']);
    expect($existing->fresh()->school_class_id)->toBe($otherClass->id);

    $conflict = PendingStudentImport::query()->sole();
    expect($conflict->existing_student_id)->toBe($existing->id);
    expect($conflict->student_number)->toBe('303115');
    expect($conflict->candidate_names)->toBe(['Anne Jansen']);
});

it('detects a duplicate in a class from another school year', function () {
    $otherYearClass = SchoolClass::factory()->for(SchoolYear::factory())->create();
    Student::factory()->create(['school_class_id' => $otherYearClass->id, 'email' => 'd303115@edu.curio.nl']);

    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n");

    $this->post(route('students.import'), ['file' => $file])->assertRedirect(route('students.import.conflicts'));

    expect(PendingStudentImport::query()->count())->toBe(1);
});

it('detects a duplicate against a soft-deleted student in another class', function () {
    $otherClass = SchoolClass::factory()->create();
    Student::factory()->create(['school_class_id' => $otherClass->id, 'email' => 'd303115@edu.curio.nl'])->delete();

    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n");

    $this->post(route('students.import'), ['file' => $file]);

    expect(PendingStudentImport::query()->count())->toBe(1);
    expect(Student::withTrashed()->count())->toBe(1);
});

it('holds a student number that appears twice in the file with different names as a conflict', function () {
    $file = UploadedFile::fake()->createWithContent(
        'studenten.csv',
        "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n303115,\"Jansen, Anna\"\n303116,\"El Idrissi, Bilal\"\n"
    );

    $response = $this->post(route('students.import'), ['file' => $file]);

    $response->assertRedirect(route('students.import.conflicts'));
    expect(Student::query()->get()->pluck('name')->all())->toBe(['Bilal El Idrissi']);

    $conflict = PendingStudentImport::query()->sole();
    expect($conflict->candidate_names)->toBe(['Anne Jansen', 'Anna Jansen']);
    expect($conflict->existing_student_id)->toBeNull();
});

it('treats an exactly repeated row as a single student', function () {
    $file = UploadedFile::fake()->createWithContent(
        'studenten.csv',
        "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n303115,\"Jansen, Anne\"\n"
    );

    $this->post(route('students.import'), ['file' => $file])->assertRedirect(route('students.index'));

    expect(Student::query()->count())->toBe(1);
    expect(PendingStudentImport::query()->count())->toBe(0);
});

it('blocks a new import while the class still has open conflicts', function () {
    $openConflict = PendingStudentImport::factory()->create(['school_class_id' => $this->teacher->current_school_class_id]);

    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n");

    $response = $this->post(route('students.import'), ['file' => $file]);

    $response->assertRedirect(route('students.import.conflicts'));
    $response->assertSessionHasErrors('file');
    expect(Student::query()->count())->toBe(0);
    expect(PendingStudentImport::query()->pluck('id')->all())->toBe([$openConflict->id]);
});

it('still allows an import when only another class has open conflicts', function () {
    PendingStudentImport::factory()->create(['school_class_id' => SchoolClass::factory()]);

    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Studentnummer,Naam\n303115,\"Jansen, Anne\"\n");

    $this->post(route('students.import'), ['file' => $file])->assertRedirect(route('students.index'));

    expect(Student::query()->count())->toBe(1);
});
