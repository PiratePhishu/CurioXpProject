<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\XpEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<XpEntry>
 */
class XpEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'lesson_id' => Lesson::factory(),
            'points' => $this->faker->numberBetween(0, 300),
        ];
    }
}
