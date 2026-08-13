<?php

namespace Database\Factories;

use App\Models\Option;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Option>
 */
class OptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'name' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'content' => fake()->realText(100),
            'explanation' => fake()->realText(40),
            'image' => 'https://placehold.co/600x400',
            'correct_answer' => false,
            'questions_id' => 1,
        ];
    }
}
