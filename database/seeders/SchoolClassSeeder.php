<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolYear = SchoolYear::query()->firstOrCreate(['name' => SchoolYear::currentAcademicYearLabel()]);

        SchoolClass::query()->firstOrCreate([
            'school_year_id' => $schoolYear->id,
            'name' => 'Demo klas',
        ]);
    }
}
