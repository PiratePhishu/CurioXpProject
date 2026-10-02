<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'L'.$this->faker->unique()->numberBetween(1, 1000),
            'week' => (string) $this->faker->numberBetween(1, 16),
            'name' => $this->faker->sentence(3),
            'max_points' => $this->faker->randomElement([200, 250, 300]),
            'position' => $this->faker->unique()->numberBetween(1, 1000),
        ];
    }
}
