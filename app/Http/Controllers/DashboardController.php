<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

use App\Models\{BranchOfMedicine, Questions, Quiz, SkillsForQuestion, Specialty, User};

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
