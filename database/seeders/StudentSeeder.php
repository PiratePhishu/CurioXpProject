<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Number of students grouped into each team (e.g. A, B, C...).
     */
    private const TEAM_SIZE = 4;

    /**
     * Preview password for seeded student accounts. Real accounts will later be
     * imported from Excel and students will be required to change this on first login.
     */
    private const PREVIEW_PASSWORD = 'Welkom123!';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Student::query()->exists()) {
            return;
        }

        $schoolClass = SchoolClass::query()->firstOrFail();

        for ($position = 1; $position <= 24; $position++) {
            Student::query()->create([
                'school_class_id' => $schoolClass->id,
                'name' => "Student {$position}",
                'team' => chr(65 + intdiv($position - 1, self::TEAM_SIZE)),
                'position' => $position,
                'email' => "student{$position}@curio-demo.test",
                'password' => self::PREVIEW_PASSWORD,
                'must_change_password' => true,
            ]);
        }
    }
}
