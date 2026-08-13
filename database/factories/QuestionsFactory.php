<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Questions;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Questions>
 */
class QuestionsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => fake()->realText(10),
            'topic' => fake()->realText(30),
            'image' => asset('assets/img/avatars/1.png'),
            'solved' => true,
            'high_yield' => true,
            'main_explanation' => fake()->realText(50),
            'elo_correct' => '4',
            'elo_incorrect' => '5',
            'start_time' => fake()->time(),
            'end_time' => fake()->time(),
            'difficulty' => fake()->randomElement(['hard', 'easy', 'medium', 'nerd']),
            'length' => fake()->randomElement(['short', 'medium', 'long']),
            'reference' => 'UW',
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($question) {

            $correctIndex = rand(0, 3);
            for ($i = 0; $i < 4; $i++) {
                Option::create([
                    'questions_id' => $question->id,
                    'name' => fake()->randomElement(['A', 'B', 'C', 'D']),
                    'content' => fake()->sentence(),
                    'explanation' => fake()->sentence(),
                    'correct_answer' => $i === $correctIndex,
                ]);
            }
        });
    }
}
