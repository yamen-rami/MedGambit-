<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\{BranchOfMedicine, Questions, Quiz, SkillsForQuestion, Specialty};
use App\Services\QuizService;

class QuizController extends Controller
{
    //
    public function __construct(protected QuizService $quizService) {}

    public function index(Request $request)
    {
        $query = Quiz::query()->with(['questions', 'questions.options']);
        if ($request->has('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }
        $quizez = $query->orderBy('id', $request->sort ?? 'desc')->paginate(30);
        $quizez->append($request->all());
        $sort = $request->sort ?? 'desc';

        return view('quiz.index', compact('quizez', 'sort'));
    }

    // Create Fun
    public function create()
    {
        // Limiting what you want just (id , name);
        // $questions = Questions::select('id', 'content')->get();
        $branches = BranchOfMedicine::all();
        $skills = SkillsForQuestion::all();
        $speciality = Specialty::all();

        return view('quiz.create', compact('branches', 'skills', 'speciality'));
    }

    public function show(int $id)
    {
        $quiz = Quiz::findOrFail($id);

        return view('quiz.show', compact('quiz'));
    }

    public function edit(int $id)
    {
        $quiz = Quiz::with(['questions'])->findOrFail($id);
        $branches = BranchOfMedicine::all();
        $skills = SkillsForQuestion::all();
        $speciality = Specialty::all();

        return view('quiz.edit', compact('quiz', 'branches', 'skills', 'speciality'));
    }

    // Start Quiz
    public function startQuiz()
    {
        return view('home.quizStart');
    }

    public function showQuiz(Quiz $quiz)
    {
        return view('home.quiz.start-quiz', compact('quiz'));
    }

    public function startRandomQuiz()
    {
        $quiz = $this->quizService->randomQuiz();
        session()->forget([
            'answers',
            'current',
        ]);

        return redirect()->route('show.quiz', $quiz);
    }

    public function randomQuiz(Quiz $quiz)
    {
        return view('home.quiz.start-quiz', [
            'quiz' => $quiz,
        ]);
    }

    // Random funcitonality
    public function quizResult(Quiz $quiz)
    {
       
        $array = [];
        $quiz->loadMissing('attempts', "questions.options");
        $attempt = $quiz->attempts()->where('user_id', auth()->id())->latest()->first();
        $answers = $attempt->answers;
        $answers->loadMissing('question.options', 'question.correctAnswer');
        $questions = $quiz->questions;
        $correctAnswers = $answers->where("is_correct" , true);
        $wrongAnswers = $answers->where("is_correct" , false);
        foreach($answers as $answer){
            $array[] = $answer->question->id ;
        }
        $unanswered = $quiz->questions->whereNotIn("id" , $array);

        session()->forget([
            'answers',
            'current',
            'correctAnswers',
            'wrongAnswers',
        ]);

        return view('home.quiz.quizResults', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $questions,
            'answers' => $answers,
            "correctAnswers" => $correctAnswers,
            'wrongAnswers' => $wrongAnswers, 
            "unanswered" => $unanswered ,
        ]);
        // dd($attempt , $question , $quiz);
    }

    public function learningQuiz(Quiz $quiz)
    {

        return view('home.quiz.learningQuiz', [
            'quiz' => $quiz,
        ]);
    }

    public function detecatedQuiz(Quiz $quiz)
    {
        return view('home.quiz.start-quiz', [
            'quiz' => $quiz,
        ]);
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        flash()->success('Quiz Has Deleted');

        return redirect()->route('quizez.index');
    }
}
