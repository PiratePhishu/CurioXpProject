<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * Preview credentials for the teacher account.
     */
    private const EMAIL = 'docent@curio-demo.test';

    private const PASSWORD = 'Welkom123!';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Docent',
                'password' => self::PASSWORD,
                'email_verified_at' => now(),
                'must_change_password' => true,
                'current_school_class_id' => SchoolClass::query()->first()?->id,
            ]
        );
    }
}
