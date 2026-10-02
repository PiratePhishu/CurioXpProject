<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Student::query()->exists()) {
            return;
        }

        for ($position = 1; $position <= 24; $position++) {
            Student::query()->create([
                'name' => "Student {$position}",
                'team' => null,
                'position' => $position,
            ]);
        }
    }
}
