<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Exception;

use App\Models\{Answers, PlayedQuestions, Questions, Quiz, QuizAttempt, User};

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
      $userId = auth()->id();

      $questionsId = Questions::query()
        ->whereNotIn("id", function ($query) use ($userId) {
          $query->select('questions_id')->from("user_played_questions")->where("user_id", $userId);
        })
        ->inRandomOrder()
      ->limit($count)
        ->pluck("id");
      // ? Attach 
      if ($questionsId->count() < $count) {
        $remaining = $count - $questionsId->count();

        $fallbackIds = Questions::query()
          ->whereNotIn('id', $questionsId)
          ->inRandomOrder()
          ->limit($remaining)
          ->pluck('id');

        $questionsId = $questionsId->concat($fallbackIds);
      }
      $quiz->questions()->attach($questionsId);
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
    $user = auth()->user();
    $rank = $user->rank;
    $playedQuestionIds = $user->playedQuestions->pluck('id');

    $questions = Questions::with("correctAnswer")->whereIn("id", array_keys($answers))->get()->keyBy("id");

    foreach ($answers as $questionId => $optionId) {

      $question = $questions[$questionId];

      $is_correct = $question->correctAnswer?->id == $optionId;

      if ($is_correct) {
        if (!$playedQuestionIds->contains($question->id)) {
          $rank += (int) $question->elo_correct;
        }
        $score++;
      } else {
        if (!$playedQuestionIds->contains($question->id)) {
          $rank -= (int) $question->elo_incorrect;
        }
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
    DB::transaction(function () use ($answersData, $score, $quizAttempt, $userId, $now, $quizId, $rank, $user) {
      Answers::insert($answersData);
      $quizAttempt->update([
        "user_id" => $user->id,
        "finished_at" => $now,
        "time_taken" => $quizAttempt->started_at->diffInSeconds($now),
        "score" => $score,
        "status" => "completed"
      ]);
      $quiz = Quiz::with("questions")->where("id", $quizId)->first();
      $questionsId = $quiz->questions->pluck("id");
      $user->playedQuestions()->syncWithoutDetaching($questionsId);
      $rank = max(0, $rank);
      $user->update(["rank" => $rank]);
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
