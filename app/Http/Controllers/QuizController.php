<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use App\Models\{BranchOfMedicine, Questions, Quiz, QuizAttempt, SkillsForQuestion, Specialty};
use App\Services\QuizService;

class QuizController extends Controller
{
    //
    public function __construct(protected QuizService $quizService) {}
    public function index(Request $request)
    {
        $query = Quiz::query()->with(["questions", "questions.options"]);
        if ($request->has("search")) {
            $query->where("name", "LIKE", "%{$request->search}%");
        }
        $quizez = $query->orderBy("id", $request->sort ?? "desc")->paginate(30);
        $quizez->append($request->all());
        $sort = $request->sort ?? "desc";
        return view("quiz.index", compact("quizez", "sort"));
    }
    // Create Fun
    public function create()
    {
        // Limiting what you want just (id , name);
        // $questions = Questions::select('id', 'content')->get();
        $branches = BranchOfMedicine::all();
        $skills = SkillsForQuestion::all();
        $speciality = Specialty::all();
        return view("quiz.create", compact("branches", "skills", "speciality"));
    }
    public function show(int $id)
    {
        $quiz = Quiz::findOrFail($id);
        return view("quiz.show", compact("quiz"));
    }
    public function store(Request $request)
    {
        // ? Validate 
        $quiz = $request->validate([
            "content" => ["required", "string", "min:3"],
            "topic" => ["required", "string", "min:3"],
            "difficulty" => ["required", Rule::in(["easy", "meduim", "hard", "nerd"])],
            "length" => ["required", Rule::in(["short", "medium", "long"])],
            "questions_number" => ['required', "integer", "min:3", "max:32"],
            "questions" => ["required", "array"],
            "questions.*" => ["exists:questions,id"],
        ]);
    }
    public function edit(int $id)
    {
        $quiz = Quiz::with(['questions'])->findOrFail($id);
        $branches = BranchOfMedicine::all();
        $skills = SkillsForQuestion::all();
        $speciality = Specialty::all();
        return view("quiz.edit", compact("quiz", "branches", "skills", "speciality"));
    }
    // Start Quiz 
    public function startQuiz()
    {
        $branches = BranchOfMedicine::all();
        $skills = SkillsForQuestion::all();
        $specialities = Specialty::all();
        return view("home.quizStart", compact("branches", "skills", "specialities"));
    }
    public function showQuiz(Quiz $quiz)
    {
        return view('home.quiz.start-quiz', compact("quiz"));
    }
    public function startRandomQuiz()
    {
        $quiz = $this->quizService->randomQuiz();
        session()->forget([
            "answers",
            "current"
        ]);
        return redirect()->route("show.quiz", $quiz);
    }
    public function randomQuiz(Quiz $quiz)
    {
        return view("home.quiz.start-quiz", [
            "quiz" => $quiz
        ]);
    }
    // Random funcitonality 
    public function quizResult(Quiz $quiz)
    {
        /*
            1- quizAttempts with the current user id 
            2- Get The Questions 
            3- get Wrong Answers 
            4- Get Right Answers 
            5- Get Time Spend
        */
        $quiz->loadMissing("attempts",);
        $attempt = $quiz->attempts()->where("user_id", auth()->id())->latest()->first();
        $answers = $attempt->answers;
        $answers->loadMissing("question.options", "question.correctAnswer");
        $questions = $quiz->questions;

        return view("home.quiz.quizResults", [
            "quiz" => $quiz,
            "attempt" => $attempt,
            "questions" => $questions,
            "answers" => $answers,
        ]);
        // dd($attempt , $question , $quiz);
    }

    public function detecatedQuiz(Quiz $quiz)
    {
        return view("home.quiz.start-quiz", [
            "quiz" => $quiz
        ]);
    }
}
