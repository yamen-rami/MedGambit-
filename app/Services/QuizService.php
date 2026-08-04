<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Exception;

use App\Models\{Answers, Questions, Quiz, QuizAttempt};

class QuizService
{
  public function randomQuiz(int $count = 20, $difficulty = "medium", $length = "short")
  {
    return DB::transaction(function () use ($count) {
      $quiz = Quiz::create([
        "name" => "random",
        "topic" => "Random Topic",
        "type" => 'random',
        "difficulty" => $difficulty ?? "easy",
        "length" => $length ?? "short",
        "questions_number" => $count ?? 3,
      ]);

      $quiz_attempt = $quiz->attempts()->create([
        "user_id" => auth()->id(),
        "started_at" => now(),
        "finished_at" => now()->addMinutes(30),
        "time_taken" => 0,
        "score" => 0,
        "status" => "pending"
      ]);

      $quesitonsId = Questions::query()
        ->inRandomOrder()
        ->limit($count)
        ->pluck("id");
      // ? Attach 
      $quiz->questions()->attachOrFail($quesitonsId);
      return $quiz;
    });
  }

  public function updateAttempt($userId, $quizId, $answers)
  {
    /* 
    1- ? Searching For Attempt 
    2- see the correct answers 
    3- update Attempt 
    4- create a answers for that attempt with question id 
    */
    $quizAttempt = QuizAttempt::where("quiz_id", $quizId)
      ->where("user_id", $userId)
      ->where("status", "pending")
      ->firstOrFail();
    if (! $quizAttempt) {
      throw new Exception('Quiz attempt not found.');
    }
    $now = now();

    $score = 0;
    // 0 ++ $score l
    $wrongAnswers = 0;
    $answersData = [];
    $questions = Questions::with("correctAnswer")->whereIn("id", array_keys($answers))->get()->keyBy("id");
    foreach ($answers as $questionId => $optionId) {
      $question = $questions[$questionId];
      $is_correct = $question->correctAnswer?->id == $optionId;
      if ($is_correct) {
        $score++;
      } else {
        $wrongAnswers++;
      }
      $answersData[] = [
        'quiz_attempt_id' => $quizAttempt->id,
        "question_id" => $questionId,
        "option_id" => $optionId,
        "time_spent" => $now,
        "is_correct" => $is_correct,
        "status" => "answered",
        "created_at" => $now,
        "updated_at" => $now,
      ];
    }
    DB::transaction(function () use ($answersData, $score, $quizAttempt, $userId, $now) {
      Answers::insert($answersData);
      $quizAttempt->update([
        "user_id" => auth()->id(),
        "finished_at" => $now,
        "time_taken" => $quizAttempt->started_at->diffInSeconds($now),
        "score" => $score,
        "status" => "completed"
      ]);
    });
    // $question -> 
    return [
      "score" => $score,
      "time_taken" => $quizAttempt->time_taken,
      "wrong_answers" => $quizAttempt->quiz->questions->count() - $score,
    ];
  }
  public function detectedQuiz(Collection $questions, $length = "short", $count = 3, $difficulty = "easy", $duration)
  {
    // Start A Quiz 
    // give the quiz type detected 
    // Start A Quiz 
    $quiz = Quiz::create([
      "name" => "Detected Topic",
      "topic" => "Detected Topic",
      "type" => "detected",
      "duration" => $duration ?? null,
      "difficulty" => $difficulty,
      "length" => $length,
      "questions_number" => $count ?? 3,
    ]);
    // 
    $quiz_attempt = $quiz->attempts()->create([
      "user_id" => auth()->id(),
      "started_at" => now(),
      "finished_at" => now()->addSeconds($duration) ?? null,
      "time_taken" => 0,
      "score" => 0,
      "status" => "pending"
    ]);
    $quiz->questions()->attachOrFail($questions);
    return $quiz;
  }
  public function learningQuiz(Collection $questions, $length = "short", $count = 3, $difficulty = "easy")
  {
    // Create Quiz 
    // Learning Quiz 
    // 
    $quiz = Quiz::create([
      "name" => "Detected Learning Quiz ",
      "topic" => "Detected Learning Quiz ",
      "type" => "learning",
      "duration" => null,
      "difficulty" => $difficulty,
      "length" => $length,
      "questions_number" => $count ?? 3,
    ]);
    // 
    $quiz_attempt = $quiz->attempts()->create([
      "user_id" => auth()->id(),
      "started_at" => now(),
      "finished_at" => now(),
      "time_taken" => 0,
      "score" => 0,
      "status" => "pending"
    ]);
    // And That is the hole case 
    $quiz->questions()->attachOrFail($questions);
    return $quiz;
  }
}
