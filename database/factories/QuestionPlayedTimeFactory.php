<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\{QuestionPlayedTime, Questions};

/**
 * @extends Factory<QuestionPlayedTime>
 */
class QuestionPlayedTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "question_id" => Questions::factory()
            "count" => fake()->numberBetween(1,100),
        ];
    }
}
