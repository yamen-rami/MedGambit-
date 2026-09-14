<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\{Questions, Quiz};
use App\Services\QuizService;

class QuizController extends Controller
{
    //'
    public function __construct(protected QuizService $quizService) {}
    
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'difficulty' => ['nullable', 'in:easy,medium,hard,nerd'],
            'length' => ['nullable', 'in:short,medium,long'],
            'sort' => ['nullable', 'in:asc,desc'],
        ]);

        $sort = $filters['sort'] ?? 'desc';
        $query = Quiz::query()->withCount('questions')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('topic', 'like', "%{$search}%");
            }))
            ->when($filters['difficulty'] ?? null, fn ($q, $value) => $q->where('difficulty', $value))
            ->when($filters['length'] ?? null, fn ($q, $value) => $q->where('length', $value));

        $quizez = $query->orderBy('id', $sort)->paginate(30)->withQueryString();

        return view('quiz.index', compact('quizez', 'sort'));
    }

    // Create Fun
    public function create()
    {
        return view('quiz.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3'],
            'topic' => ['required', 'string', 'min:3'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard', 'nerd'])],
            'length' => ['required', Rule::in(['short', 'medium', 'long'])],
            'type' => ['required', Rule::in(['random', 'detected', 'admin', 'learning', 'game'])],
            'questions_number' => ['required', 'integer', 'min:3', 'max:20'],
            'duration' => ['nullable', 'integer', 'min:1'],
        ]);

        Quiz::create($data);
        flash()->success('Quiz Has Created Successfully');

        return redirect()->route('quizez.index');
    }

    public function show(int $id)
    {
        $quiz = Quiz::findOrFail($id);

        return view('quiz.show', compact('quiz'));
    }

    public function edit(int $id)
    {
        $quiz = Quiz::with(['questions'])->findOrFail($id);

        return view('quiz.edit', compact('quiz'));
    }

    public function update(Request $request, Quiz $quizez)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3'],
            'topic' => ['required', 'string', 'min:3'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard', 'nerd'])],
            'length' => ['required', Rule::in(['short', 'medium', 'long'])],
            'type' => ['required', Rule::in(['random', 'detected', 'admin', 'learning', 'game'])],
            'questions_number' => ['required', 'integer', 'min:3', 'max:20'],
            'duration' => ['nullable', 'integer', 'min:1'],
        ]);

        $quizez->update($data);
        flash()->info('Quiz Has Updated Successfully');

        return redirect()->route('quizez.index');
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
        $attempt = $quiz->attempts()
            ->where('user_id', auth()->id())
            ->where('status', 'finished')
            ->latest()
            ->first();

        if (! $attempt) {
            abort(403, 'Finish your attempt');
        }
        // getting answers with questions and options

        $answers = $attempt->answers()
            ->with([
                'question.options',
                'question.correctAnswer',
            ])
            ->get();

        $correctAnswers = $answers->where('is_correct', true);
        $wrongAnswers = $answers->where('is_correct', false);

        $answeredQuestionIds = $answers->pluck('question_id');

        $unanswered = $quiz->questions()
            ->whereNotIn('questions.id', $answeredQuestionIds)
            ->with('options')
            ->get();

        return view('home.quiz.quizResults', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'answers' => $answers,
            'correctAnswers' => $correctAnswers,
            'wrongAnswers' => $wrongAnswers,
            'unanswered' => $unanswered,
        ]);
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

    public function destroy(Quiz $quizez)
    {
        $quizez->delete();
        flash()->success('Quiz Has Deleted');

        return redirect()->route('quizez.index');
    }
}
