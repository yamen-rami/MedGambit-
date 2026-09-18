<?php

namespace App\Services;

use App\Models\Answers;
use App\Models\Questions;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QuizService
{
    public function randomQuiz(int $count = 20, $difficulty = 'medium', $length = 'short')
    {
        return DB::transaction(function () use ($count, $difficulty, $length) {
            $quiz = Quiz::create([
                'name' => 'random',
                'topic' => 'Random Topic',
                'type' => 'random',
                'difficulty' => $difficulty ? $difficulty : 'easy',
                'length' => $length ? $length : 'short',
                'questions_number' => $count ?? 3,
            ]);

            $quiz_attempt = $quiz->attempts()->create([
                'user_id' => auth()->id(),
                'started_at' => now(),
                'finished_at' => now()->addMinutes(30),
                'time_taken' => 0,
                'score' => 0,
                'status' => 'pending',
            ]);
            $userId = auth()->id();

            $questionsId = Questions::query()
                ->whereNotIn('id', function ($query) use ($userId) {
                    $query->select('questions_id')->from('user_played_questions')->where('user_id', $userId);
                })
                ->inRandomOrder()
                ->limit($count)
                ->pluck('id');
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
        //
        $quizAttempt = QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();
        if (! $quizAttempt) {
            throw new Exception('Quiz attempt not found.');
        }
        $now = now();

        $score = 0;
        $wrongAnswers = 0;
        $user = auth()->user()->loadMissing('playedQuestions');
        $rank = $user->rank;
        $playedQuestionIds = $user->playedQuestions->pluck('id');
        $quiz = Quiz::with('questions')->findOrFail($quizId);
        $quizQuestionIds = $quiz->questions->pluck('id')->map(fn ($id) => (int) $id)->all();
        $questions = Questions::with('correctAnswer', 'options', 'playedCount')
            ->whereIn('id', $quizQuestionIds)
            ->get()
            ->keyBy('id');
        foreach ($answers as $questionId => $optionId) {

            $question = $questions->get($questionId);
            if (! $question || ! $question->options->contains('id', (int) $optionId)) {
                continue;
            }
            $question->playedCount()->firstOrCreate([], ['count' => 0])->increment('count', 1);
            $is_correct = $question->correctAnswer?->id == $optionId;

            if ($is_correct) {
                if (! $playedQuestionIds->contains($question->id)) {
                    $rank += (int) $question->elo_correct;
                }
                $score++;
            } else {
                if (! $playedQuestionIds->contains($question->id)) {
                    $rank -= (int) $question->elo_incorrect;
                }
                $wrongAnswers++;
            }

        }
        DB::transaction(function () use ($score, $quizAttempt, $now, $quiz, $rank, $user) {
            $quizAttempt->update([
                'user_id' => $user->id,
                'finished_at' => $now,
                'time_taken' => $quizAttempt->started_at?->diffInSeconds($now),
                'score' => $score,
                'status' => 'finished',
                'current_rank' => $user->rank,
                'new_rank' => $rank,
            ]);
            $questionsId = $quiz->questions->pluck('id');
            $user->playedQuestions()->syncWithoutDetaching($questionsId);
            $rank = max(0, $rank);
            $user->update(['rank' => $rank]);
        });

        // $question ->
        return [
            'score' => $score,
            'time_taken' => $quizAttempt->time_taken,
            'wrong_answers' => $quiz->questions->count() - $score,
        ];
    }

    public function updateAttemptLearning($userId, $quizId, $answers)
    {
        /*
            1- ? Searching For Attempt
            2- see the correct answers
            3- update Attempt
            4- create a answers for that attempt with question id
        */
        //
        $quizAttempt = QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();
        if (! $quizAttempt) {
            throw new Exception('Quiz attempt not found.');
        }
        $now = now();

        $score = 0;
        $wrongAnswers = 0;
        $user = auth()->user()->loadMissing('playedQuestions');
        $playedQuestionIds = $user->playedQuestions->pluck('id');
        $quiz = Quiz::with('questions')->findOrFail($quizId);
        $quizQuestionIds = $quiz->questions->pluck('id')->map(fn ($id) => (int) $id)->all();
        $questions = Questions::with('correctAnswer', 'options', 'playedCount')
            ->whereIn('id', $quizQuestionIds)
            ->get()
            ->keyBy('id');
        foreach ($answers as $questionId => $optionId) {

            $question = $questions->get($questionId);
            if (! $question || ! $question->options->contains('id', (int) $optionId)) {
                continue;
            }
            $question->playedCount()->firstOrCreate([], ['count' => 0])->increment('count', 1);
            $is_correct = $question->correctAnswer?->id == $optionId;

            if ($is_correct) {
                $score++;
            } else {
                $wrongAnswers++;
            }

        }
        DB::transaction(function () use ($score, $quizAttempt, $now, $quiz, $user) {
            $quizAttempt->update([
                'user_id' => $user->id,
                'finished_at' => $now,
                'time_taken' => $quizAttempt->started_at?->diffInSeconds($now),
                'score' => $score,
                'status' => 'finished',
            ]);
            $questionsId = $quiz->questions->pluck('id');
            $user->playedQuestions()->syncWithoutDetaching($questionsId);
        });

        // $question ->
        return [
            'score' => $score,
            'time_taken' => $quizAttempt->time_taken,
            'wrong_answers' => $quiz->questions->count() - $score,
        ];
    }

    public function detectedQuiz(Collection $questions, ?string $name = null, $length = null, $count = null, $difficulty = null, ?int $duration = null)
    {
        // Start A Quiz
        // give the quiz type detected
        // Start A Quiz
        $quiz = Quiz::create([
            'name' => $name ?? 'System Exam Mode',
            'topic' => 'System Exam Mode',
            'type' => 'detected',
            'duration' => $duration ? $duration : null,
            'difficulty' => $difficulty ? $difficulty : 'easy',
            'length' => $length ? $length : 'short',
            'questions_number' => $count ?? 3,
        ]);
        //
        $quiz_attempt = $quiz->attempts()->create([
            'user_id' => auth()->id(),
            'started_at' => now(),
            'finished_at' => $duration ? now()->addSeconds($duration) : null,
            'time_taken' => 0,
            'score' => 0,
            'status' => 'pending',
        ]);
        $quiz->questions()->attachOrFail($questions);

        return $quiz;
    }

    public function learningQuiz(Collection $questions, ?string $name = null, $length = 'short', $count = 3, $difficulty = 'easy')
    {
        $quiz = Quiz::create([
            'name' => $name ?? 'System Learning Quiz',
            'topic' => 'System Learning Quiz',
            'type' => 'learning',
            'duration' => null,
            'difficulty' => $difficulty ? $difficulty : 'easy',
            'length' => $length ? $length : 'easy',
            'questions_number' => $count ?? 3,
        ]);
        //
        $quiz_attempt = $quiz->attempts()->create([
            'user_id' => auth()->id(),
            'started_at' => now(),
            'finished_at' => now(),
            'time_taken' => 0,
            'score' => 0,
            'status' => 'pending',
        ]);
        $quiz->questions()->attachOrFail($questions);

        return $quiz;
    }
}
