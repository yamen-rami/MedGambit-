<?php
use App\Models\Questions;
use App\Models\QuizAttempt;
use App\Services\QuizService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $quiz,
        $currentQuestion,
        $attempt,
        $activeOptionId = null;
    public int $correctCount = 0,
        $wrongCount = 0,
        $current = 1;
    public array $answers = [];
    public array $answerResults = [];

    public function mount($quiz)
    {
        $this->quiz = $quiz->loadMissing(['questions']);
        $this->attempt = QuizAttempt::with('answers')
            ->where('user_id', auth()->id())
            ->where('quiz_id', $this->quiz->id)
            ->first();
        if (!$this->attempt) {
            abort(402, 'Something went wrong');
        }
        $this->current = $this->attempt->current;
        $this->correctCount = $this->attempt->correctCount ?? 0;
        $this->wrongCount = $this->attempt->wrongCount ?? 0;
        $this->answers = $this->attempt->answers->pluck('option_id', 'question_id')->toArray();
        $this->answerResults = $this->attempt->answers->pluck('is_correct', 'question_id')->toArray();
        $this->loadQuestion();
    }

    public function loadQuestion()
    {
        $questionId = $this->quiz->questions->get($this->current - 1)?->id;
        if (!$questionId) {
            return;
        }
        $this->currentQuestion = Questions::with(['options', 'correctAnswer'])->findOrFail($questionId);
        $this->activeOptionId = $this->answers[$questionId] ?? null;
    }

    #[Computed]
    public function count()
    {
        return $this->quiz->questions->count();
    }
    #[Computed]
    public function currentElo()
    {
        return $this->currentQuestion->elo_correct ?? 0;
    }
    #[Computed]
    public function currentInCorrectElo()
    {
        return $this->currentQuestion->elo_incorrect ?? 0;
    }

    public function submit($optionId, $questionId)
    {
        if (array_key_exists($questionId, $this->answers)) {
            return;
        }
        $question = $this->currentQuestion;
        if (!$question || (int) $question->id !== (int) $questionId || !$question->options->contains('id', (int) $optionId) || !$this->attempt) {
            return;
        }
        $isCorrect = $question->correctAnswer?->id === (int) $optionId;
        $isCorrect ? $this->correctCount++ : $this->wrongCount++;
        $this->attempt->increment($isCorrect ? 'correctCount' : 'wrongCount');
        $this->attempt->answers()->updateOrCreate(['question_id' => $questionId], ['status' => 'answered', 'is_correct' => $isCorrect, 'option_id' => $optionId]);
        $this->answers[$questionId] = (int) $optionId;
        $this->answerResults[$questionId] = $isCorrect;
        $this->activeOptionId = (int) $optionId;
    }

    public function next()
    {
        if ($this->current < $this->count) {
            $this->updateCurrent($this->current + 1);
        }
    }
    public function previous()
    {
        if ($this->current > 1) {
            $this->updateCurrent($this->current - 1);
        }
    }
    public function updateCurrent($current)
    {
        if ($current < 1 || $this->count < $current) {
            return;
        }
        $this->current = (int) $current;
        $this->attempt->current = (int) $current;
        $this->attempt->save();
        $this->loadQuestion();
    }

    #[On('quit-quiz')]
    public function quitQuiz()
    {
        app(QuizService::class)->updateAttemptLearning(auth()->id(), $this->quiz->id, $this->answers);
        return redirect()->route('quizResult', $this->quiz);
    }
    public function submitAttempt()
    {
        $this->validate(['answers' => ['required', 'array', 'min:' . $this->count]]);
        if ($this->attempt->status === 'finished') {
            return;
        }
        app(QuizService::class)->updateAttemptLearning(auth()->id(), $this->quiz->id, $this->answers);
        return redirect()->route('quizResult', $this->quiz);
    }
};
?>
@php
    $questionCount = $this->count;
    $questionNumber = $this->current;
    $answeredCount = count($answers);
    $remainingCount = max(0, $questionCount - $answeredCount);
    $progress = $questionCount ? round(($answeredCount / $questionCount) * 100) : 0;
    $answeredOptionId = $currentQuestion ? $answers[$currentQuestion->id] ?? null : null;
    $hasAnswered = $answeredOptionId !== null;
@endphp
<main class="brand-main">
    <button class="sidebar-trigger" id="sidebarTrigger" type="button"><span
            class="material-symbols-outlined">format_list_numbered</span> Quiz progress <span
            class="material-symbols-outlined">chevron_right</span></button>
    <div class="brand-layout">
        <aside class="question-sidebar" id="questionSidebar" aria-label="Quiz progress">
            <div class="sidebar-heading">
                <div><span>QUIZ PROGRESS</span><strong>{{ $quiz->name ?: 'Medical quiz' }}</strong></div><button
                    id="sidebarClose" type="button" aria-label="Close progress"><span
                        class="material-symbols-outlined">close</span></button>
            </div>
            <div class="progress-summary">
                <div><b>Question <em>{{ $questionNumber }}</em></b><span>of {{ $questionCount }}</span></div>
                <strong>{{ $progress }}%</strong>
            </div>
            <div class="progress-track"><span style="width: {{ $progress }}%"></span></div>
            <div class="score-grid">
                <span><b>{{ $correctCount }}</b>Correct</span><span><b>{{ $wrongCount }}</b>Wrong</span><span><b>{{ $remainingCount }}</b>Remain</span>
            </div>
            <div class="sidebar-meta"><span><span class="small-empty-circle"></span> LEARNING MODE</span><b>LEARNING
                    ON</b></div>
            <div class="question-list" id="questionList">
                @foreach ($quiz->questions as $navQuestion)
                    @php($navAnswerId = $answers[$navQuestion->id] ?? null)
                    @php($navIsCorrect = (bool) ($answerResults[$navQuestion->id] ?? false))
                    <button type="button" wire:click="updateCurrent({{ $loop->iteration }})"
                        class="question-row {{ $questionNumber === $loop->iteration ? 'current' : '' }} {{ $navAnswerId !== null ? ($navIsCorrect ? 'correct' : 'wrong') : '' }}"
                        @if ($questionNumber === $loop->iteration) aria-current="step" @endif><span
                            class="row-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span
                            class="row-title">{{ $navQuestion->name ?: strip_tags($navQuestion->topic ?: 'Clinical question') }}</span><i
                            class="row-status bi {{ $navAnswerId === null ? 'bi-circle' : ($navIsCorrect ? 'bi-check-circle-fill' : 'bi-x-circle-fill') }}"></i></button>
                @endforeach
            </div>
        </aside>
        <div class="question-backdrop" id="questionBackdrop"></div>
        <section class="question-panel" aria-labelledby="questionTitle">
            <div class="panel-scroll">
                <div class="panel-topline"><span>QUESTION {{ $questionNumber }} · {!! $currentQuestion?->topic !!}</span><span
                        class="difficulty">DIFFICULTY ·
                        {{ strtoupper($currentQuestion?->difficulty ?: $quiz->difficulty ?: 'MEDIUM') }}</span></div>
                <h2 id="questionTitle">{{ $currentQuestion?->name ?: 'Clinical question' }}</h2>
                <div class="case-vignette">{!! $currentQuestion?->content !!}</div>
                <div class="answer-list" role="radiogroup" aria-label="Answer options">
                    @foreach ($currentQuestion?->options ?? [] as $option)
                        @php($isSelected = (int) $answeredOptionId === (int) $option->id)
                        @php($isCorrect = (int) $currentQuestion->correctAnswer?->id === (int) $option->id)
                        <div
                            class="answer-item answer-option {{ $isSelected ? ($isCorrect ? 'correct' : 'incorrect') : '' }} {{ $hasAnswered && $isCorrect ? 'correct-answer' : '' }}">
                            <button class="answer-choice" type="button"
                                wire:click="submit({{ $option->id }}, {{ $currentQuestion->id }})"
                                aria-checked="{{ $isSelected ? 'true' : 'false' }}"><span
                                    class="letter">{{ $option->name ?: chr(64 + $loop->iteration) }}</span><span>{{ $option->content }}</span><span
                                    class="radio"></span></button>
                            @if ($hasAnswered)
                                <div class="option-explanation">{!! $option->explanation !!}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if ($hasAnswered)
                    <div class="explanation-card"><strong><i class="bi bi-lightbulb-fill"></i> HIGH YIELD</strong>
                        <div>{!! $currentQuestion?->high_yield !!}</div><strong><i class="bi bi-check-circle-fill"></i> MAIN
                            EXPLANATION</strong>
                        <div>{!! $currentQuestion?->main_explanation !!}</div>
                    </div>
                @endif
            </div>
            <div class="question-actions"><button class="btn btn-outline-secondary" type="button" wire:click="previous"
                    @disabled($questionNumber === 1)><i class="bi bi-arrow-left"></i> Previous</button>
                @if ($questionNumber < $questionCount)
                    <button class="btn btn-primary" type="button" wire:click="next">Next question <i
                        class="bi bi-arrow-right"></i></button>@else<button class="btn btn-primary" type="button"
                        wire:click="submitAttempt">Submit quiz <i class="bi bi-check-lg"></i></button>
                @endif
            </div>
        </section>
    </div>
</main>
