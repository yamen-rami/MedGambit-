<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\BranchOfMedicineController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\SkillsForQuestionController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\admin;
use App\Models\Game;
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
Route::resource('references', ReferenceController::class);
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
    // TODO Game Routes

    Route::get('start/game', [GameController::class, 'startGame'])->name('start.game');
    // Route::get("friend/game/{game}" , [GameController::class , "friendGame"])->name("friendGame");
    Route::get('game/friend/{challenge_token}', [GameController::class, 'gameStarted'])->name('friendGame');

    // To Games
    Route::get('friend/challenge/{challenge_token}', [GameController::class, 'friendGameStarted'])->name('friend.game.started');
    Route::get('game/started/{game}', [GameController::class, 'gameStarted'])->name('gameStarted');

    Route::get('config/game', [GameController::class, 'config'])->name('config.game');
    // to waiting page for the player 1
    Route::get('waiting/{game}', [GameController::class, 'waiting'])->name('waiting');
});
Route::get('user/profile/{user}', [UserController::class, 'profile'])->name('user.profile');
Route::get('get/branches', [ApiController::class, 'branches'])
    ->name('getBranches');
Route::get('get/speciality', [ApiController::class, 's'])
    ->name('getSpeciality');
Route::get('get/skills', [ApiController::class, 'skills'])
    ->name('getSkills');
Route::get('get/references', [ApiController::class, 'references'])
    ->name('getReferences');

Route::get('game/results/{game}/', [GameController::class, 'gameResults'])->name('game.results');
require __DIR__.'/settings.php';
