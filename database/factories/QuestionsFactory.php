<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\{Option, Questions, Reference};

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
            'content' => fake()->sentence(),
            'topic' => fake()->sentence(),
            'image' => asset('assets/img/avatars/1.png'),
            'high_yield' => true,
            'main_explanation' => fake()->paragraph(),
            'elo_correct' => '4',
            'elo_incorrect' => '5',
            'difficulty' => fake()->randomElement(['hard', 'easy', 'medium', 'nerd']),
            'length' => fake()->randomElement(['short', 'medium', 'long']),
            'reference_id' => Reference::factory(),
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
