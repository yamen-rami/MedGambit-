<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\BranchOfMedicineController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SkillsForQuestionController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\admin;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware(['auth', 'verified', admin::class])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});
// ? Admin Dashboard
// Route::middleware([admin::class, "auth"])->group(function () {
Route::resource('questions', QuestionsController::class);
Route::get('options/{option}/edit', [OptionsController::class, 'edit'])->name('options.edit');
Route::patch('options/update/{option}', [OptionsController::class, 'update'])->name('options.update');
Route::resource('quizez', QuizController::class);
Route::resource('speciality', SpecialtyController::class);
Route::resource('branch', BranchOfMedicineController::class);
Route::resource('skills', SkillsForQuestionController::class);
Route::get('option/create/{id}', [OptionsController::class, 'create'])->name('option.create');
Route::post('option/store/{id}', [OptionsController::class, 'store'])->name('option.store');
Route::delete('option/destory/{optionId}/questionId/{questionId}', [OptionsController::class, 'destroy'])->name('option.destroy');
// });
// ? User Dashboard
Route::middleware('auth')->group(function () {
    Route::get('start/quiz', [QuizController::class, 'startQuiz'])->name('start.quiz');
    Route::get('show/quizResult/{quiz}', [QuizController::class, 'quizResult'])->name('quizResult');
    Route::get('create/random/quiz', [QuizController::class, 'startRandomQuiz'])->name('start.random.quiz');
    Route::get('random/quiz/{quiz}', [QuizController::class, 'randomQuiz'])->name('random.quiz');
    Route::get('detecated/quiz/{quiz}', [QuizController::class, 'detecatedQuiz'])->name('start.detecated.quiz');
    Route::get('detecated/learning/{quiz}', [QuizController::class, 'learningQuiz'])->name('start.learning.quiz');
    Route::get('show/quiz/{quiz}', [QuizController::class, 'showQuiz'])->name('show.quiz');
    // Route::get("start/game/quiz" , [Game])
});
Route::get('user/profile/{user}', [UserController::class, 'profile'])->name('user.profile');
Route::get('get/branches', [ApiController::class, 'branches'])
    ->name('getBranches');
Route::get('get/speciality', [ApiController::class, 's'])
    ->name('getSpeciality');
Route::get('get/skills', [ApiController::class, 'skills'])
    ->name('getSkills');
require __DIR__.'/settings.php';
