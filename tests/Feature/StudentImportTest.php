<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('imports new students from a csv file', function () {
    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Naam\nAnne Jansen\nBilal El Idrissi\n");

    $response = $this->post(route('students.import'), ['file' => $file]);

    $response->assertRedirect(route('students.index'));

    $names = Student::query()->get()->pluck('name');
    expect($names)->toContain('Anne Jansen');
    expect($names)->toContain('Bilal El Idrissi');

    $anne = Student::query()->get()->sole(fn (Student $student) => $student->name === 'Anne Jansen');
    expect($anne->email)->not->toBeNull();
    expect($anne->must_change_password)->toBeTrue();
});

it('updates an existing student instead of creating a duplicate', function () {
    $existing = Student::factory()->create(['name' => 'Anne Jansen']);

    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Naam\nAnne Jansen\n");

    $this->post(route('students.import'), ['file' => $file]);

    $matches = Student::query()->get()->filter(fn (Student $student) => $student->name === 'Anne Jansen');
    expect($matches)->toHaveCount(1);
    expect($existing->fresh()->email)->toBe($existing->email);
});

it('assigns unique emails when different names slugify the same way', function () {
    $file = UploadedFile::fake()->createWithContent('studenten.csv', "Naam\nJan de Vries\nJan de Vries!!\n");

    $this->post(route('students.import'), ['file' => $file]);

    $emails = Student::query()->get()
        ->whereIn('name', ['Jan de Vries', 'Jan de Vries!!'])
        ->pluck('email');

    expect($emails)->toHaveCount(2);
    expect($emails->unique())->toHaveCount(2);
});
