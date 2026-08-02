<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{BranchOfMedicineController, OptionsController, QuestionsController, QuizController, SkillsForQuestionController, SpecialtyController};
use App\Http\Middleware\admin;
use App\Models\SkillsForQuestion;

Route::view('/', 'home')->name('home');

Route::middleware(['auth', 'verified', admin::class])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});
// ? Admin Dashboard
// Route::middleware([admin::class, "auth"])->group(function () {
Route::resource("questions", QuestionsController::class);
Route::get("options/{option}/edit", [OptionsController::class, "edit"])->name("options.edit");
Route::patch("options/update/{option}", [OptionsController::class, "update"])->name("options.update");
Route::resource("quizez", QuizController::class);
Route::resource("speciality", SpecialtyController::class);
Route::resource("branch", BranchOfMedicineController::class);
Route::resource("skills", SkillsForQuestionController::class);
Route::get("option/create/{id}", [OptionsController::class, "create"])->name("option.create");
Route::post("option/store/{id}", [OptionsController::class, "store"])->name("option.store");
Route::delete("option/destory/{optionId}/questionId/{questionId}", [OptionsController::class, "destroy"])->name("option.destroy");
// });
// ? User Dashboard
Route::middleware("auth")->group(function () {
    Route::get("start/quiz", [QuizController::class, "startQuiz"])->name("start.quiz");
    Route::get("show/quizResult/{quiz}", [QuizController::class, "quizResult"])->name("quizResult");
    Route::get("create/random/quiz", [QuizController::class, "startRandomQuiz"])->name("start.random.quiz");
    Route::get("random/quiz/{quiz}", [QuizController::class, "randomQuiz"])->name("random.quiz");
    Route::get("detecated/quiz/{quiz}", [QuizController::class, "detecatedQuiz"])->name("start.detecated.quiz");

    Route::get("show/quiz/{quiz}", [QuizController::class, "showQuiz"])->name("show.quiz");
});




require __DIR__ . '/settings.php';
