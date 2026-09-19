<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\{Quiz, QuizAttempt};

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
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
            "name" => "yamen testing", 
            "topic" => "yamen testing topic",
            "questions_number" => 20 , 
            "difficulty" => "hard",
            "length" => "short",
            "type" => "learning",
        ];
    }
    public function configure()
    {
        return $this->afterCreating(function ($quiz) {  
            QuizAttempt::create([
                "quiz_id" => $quiz->id , 
                "user_id" => 1 ,
                "wrongCount" => 2 , 
                "correctCount" => 18 , 
                "new_rank" => 1700 ,
                "current_rank" =>1600 , 
                "current" => 1 ,
                "score" => 18 , 
                "time_taken" => 3600 ,
                'status' => "finished",
                "started_at" => now() ,
                'finished_at' => now()->addHour(),
            ]);
        });
    }
}
