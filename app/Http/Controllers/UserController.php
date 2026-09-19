<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function profile(User $user)
    {   
        $attemptStats = $user->attempts()
            ->selectRaw("count(*) as total,
                sum(case when status = 'finished' then 1 else 0 end) as completed,
                sum(case when status = 'pending' then 1 else 0 end) as incomplete")
            ->first();

        $stats = (object) [
            'total' => (int) ($attemptStats->total ?? 0),
            'completed' => (int) ($attemptStats->completed ?? 0),
            'incomplete' => (int) ($attemptStats->incomplete ?? 0),
        ];

        $answerTotals = $user->attempts()
            ->where('quiz_attempts.status', 'finished')
            ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->selectRaw('coalesce(sum(quiz_attempts.score), 0) as correct, coalesce(sum(quizzes.questions_number), 0) as answered')
            ->first();
        $correctAnswers = (int) ($answerTotals->correct ?? 0);
        $questionCount = (int) ($answerTotals->answered ?? 0);

        $answerStats = (object) [
            'correct' => $correctAnswers,
            'wrong' => max(0, $questionCount - $correctAnswers),
            'answered' => $questionCount,
        ];
        $rankPosition = User::where('rank', '>', $user->rank)->count() + 1;
        $user->loadCount('playedQuestions');

        return view('user.profile', compact('user', 'stats', 'answerStats', 'rankPosition'));
    }
    //
}
