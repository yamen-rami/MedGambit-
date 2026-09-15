<?php

namespace App\Http\Controllers;

use App\Models\BranchOfMedicine;
use App\Models\Questions;
use App\Models\Quiz;
use App\Models\SkillsForQuestion;
use App\Models\Specialty;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'counts' => [
                'users' => User::count(),
                'questions' => Questions::count(),
                'quizzes' => Quiz::count(),
                'branches' => BranchOfMedicine::count(),
                'specialities' => Specialty::count(),
                'skills' => SkillsForQuestion::count(),

            ],
            'dashboardUsers' => User::latest()->limit(5)->get(['id', 'name', 'email', 'created_at']),
            'dashboardQuestions' => Questions::latest()->limit(5)->get(['id', 'name', 'content', 'difficulty', 'created_at']),
        ]);
    }
}
