<?php

namespace Database\Factories;

use App\Models\PendingStudentImport;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PendingStudentImport>
 */
class PendingStudentImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'school_class_id' => fn () => SchoolClass::query()->first()?->id ?? SchoolClass::factory(),
            'existing_student_id' => null,
            'student_number' => (string) $this->faker->unique()->numberBetween(300000, 399999),
            'candidate_names' => [$this->faker->name(), $this->faker->name()],
        ];
    }
}
