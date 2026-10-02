<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'team' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'position' => $this->faker->unique()->numberBetween(1, 1000),
        ];
    }
}
