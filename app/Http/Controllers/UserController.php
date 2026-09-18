<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile(Request $request, User $user)
    {
        $filter = $request->string('filter', 'all')->toString();
        $sort = $request->string('sort', 'newest')->toString();
        $filter = in_array($filter, ['all', 'completed', 'incomplete'], true) ? $filter : 'all';
        $sort = in_array($sort, ['highest', 'lowest', 'newest', 'oldest'], true) ? $sort : 'newest';

        $search = $request->string('search')->trim()->toString();
        $attemptsQuery = $user->attempts()->with('quiz:id,name,topic,type,questions_number');
        if ($search !== '') {
            $attemptsQuery->whereHas('quiz', fn ($query) => $query->where('name', 'like', "%{$search}%"));
        }
        if ($filter === 'completed') {
            $attemptsQuery->where('status', 'finished');
        } elseif ($filter === 'incomplete') {
            $attemptsQuery->where('status', 'pending');
        }
        $attempts = (match ($sort) {
            'highest' => $attemptsQuery->orderByDesc('score'),
            'lowest' => $attemptsQuery->orderBy('score'),
            'oldest' => $attemptsQuery->oldest(),
            default => $attemptsQuery->latest(),
        })->paginate(10)->withQueryString();

        $allAttempts = $user->attempts()->with('quiz:id,questions_number')->get();
        $stats = (object) [
            'total' => $allAttempts->count(),
            'completed' => $allAttempts->where('status', 'finished')->count(),
            'incomplete' => $allAttempts->where('status', 'pending')->count(),
        ];
        $finishedAttempts = $allAttempts->where('status', 'finished');
        $correctAnswers = $finishedAttempts->sum(fn ($attempt) => (int) $attempt->score);
        $questionCount = $finishedAttempts->sum(fn ($attempt) => (int) ($attempt->quiz?->questions_number ?? 0));
        $answerStats = (object) [
            'correct' => $correctAnswers,
            'wrong' => max(0, $questionCount - $correctAnswers),
            'answered' => $questionCount,
        ];
        $rankPosition = User::where('rank', '>', $user->rank)->count() + 1;
        $user->loadCount('playedQuestions');

        return view('user.profile', compact('user', 'attempts', 'filter', 'sort', 'search', 'stats', 'answerStats', 'rankPosition'));
    }
    //
}
