<?php

use App\Models\Questions;
use App\Models\QuizAttempt;
use App\Services\QuizService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $quiz;

    public $current = 1;

    public $currentQuestion;

    public array $answers = [];

    public $attempt;

    public function mount($quiz)
    {
        $this->quiz = $quiz->loadMissing('questions');

        $this->attempt = QuizAttempt::with('answers')
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->where('quiz_id', $this->quiz->id)
            ->first();

        if (!$this->attempt) {
            abort(403, 'Something went wrong.');
        }

        $this->current = $this->attempt->current;
        $this->answers = $this->attempt->answers->pluck('option_id', 'question_id')->toArray();
        $this->loadQuestion();
    }

    #[On('quit-quiz')]
    public function quitQuiz()
    {
        app(QuizService::class)->updateAttempt(auth()->id(), $this->quiz->id, $this->answers);

        return redirect()->route('quizResult', $this->quiz);
    }

    public function loadQuestion(): void
    {
        $questionId = $this->quiz->questions->get($this->current - 1)?->id;

        if (!$questionId) {
            $this->currentQuestion = null;

            return;
        }

        $this->currentQuestion = Questions::with('options', 'correctAnswer', 'playedCount', 'reference')->findOrFail($questionId);
    }

    public function hydrate(): void
    {
        $this->quiz->loadMissing('questions');
    }

    public function submit($optionId, $questionId): void
    {
        $question = $this->currentQuestion;

        if (!$question) {
            abort(403);
        }

        if ((int) $question->id !== (int) $questionId || !$question->options->contains('id', (int) $optionId)) {
            return;
        }

        if (!$this->attempt) {
            return;
        }

        $question->loadMissing('correctAnswer');
        $isCorrect = $question->correctAnswer?->id === (int) $optionId;

        $this->attempt->answers()->updateOrCreate(
            ['question_id' => $questionId],
            [
                'status' => 'answered',
                'is_correct' => $isCorrect,
                'option_id' => $optionId,
            ],
        );

        $this->answers[$questionId] = (int) $optionId;
    }

    public function next(): void
    {
        if ($this->current < $this->count) {
            $this->updateCurrent($this->current + 1);
        }
    }

    public function updateCurrent($current): void
    {
        $max = $this->quiz->questions->count();

        if ($current < 1 || $max < $current) {
            return;
        }

        $this->current = (int) $current;
        $this->attempt->current = (int) $current;
        $this->attempt->save();
        $this->loadQuestion();

    }

    public function previous(): void
    {
        if ($this->current > 1) {
            $this->updateCurrent($this->current - 1);
        }
    }

    #[Computed]
    public function count(): int
    {
        return $this->quiz->questions->count();
    }

    #[Computed]
    public function remainingSeconds(): ?int
    {
        if (!$this->attempt?->finished_at) {
            return null;
        }

        return max(0, (int) now()->diffInSeconds($this->attempt->finished_at, false));
    }

    #[Computed]
    public function currentElo()
    {
        return $this->currentQuestion?->elo_correct ?? 0;
    }

    #[Computed]
    public function currentInCorrectElo()
    {
        return $this->currentQuestion?->elo_incorrect ?? 0;
    }

    public function finishQuiz()
    {
        return $this->timerEnds();
    }

    public function timerEnds()
    {
        app(QuizService::class)->updateAttempt(auth()->id(), $this->quiz->id, $this->answers);

        return redirect()->route('quizResult', $this->quiz);
    }

    public function submitAttempt()
    {
        $questionsCount = $this->count;

        $this->validate([
            'answers' => ['required', 'array', "min:$questionsCount"],
        ]);

        app(QuizService::class)->updateAttempt(auth()->id(), $this->quiz->id, $this->answers);

        return redirect()->route('quizResult', $this->quiz);
    }
};
?>
@php
    $question = $this->currentQuestion;
    $questionCount = (int) $this->count;
    $questionNumber = (int) $this->current;
    $answeredOptionId = $question ? $answers[$question->id] ?? null : null;
    $progress = $questionCount > 1 ? (($questionNumber - 1) / ($questionCount - 1)) * 100 : 0;
    $topic = $question?->topic ?: ($quiz->topic ?: 'Clinical medicine');
@endphp

<div class="quiz-shell py-5">
    <div class="quiz-context px-4">
        <div>
            <span
                class="eyebrow">{{ strtoupper($quiz->name ?? $quiz->type == 'deteceted' ? 'Exam Mode' : 'null' ?? 'QUIZ') }}
                · {{ $quiz->name }}</span>
            <h1>{{ $quiz->name ?: 'Medical Quiz' }}</h1>
        </div>
        <div class="session-status">
            <span class="status-dot"></span>
            Question {{ $questionNumber }} of {{ $questionCount }}
            <span class="divider">·</span> {{ round($progress) }}% complete
            @if ($this->remainingSeconds !== null)
                <span class="divider">·</span>
                <span class="quiz-timer" wire:ignore x-data="{ seconds: {{ max(0, (int) $this->remainingSeconds) }} }" x-init="const timer = setInterval(() => {
                    if (seconds <= 1) {
                        clearInterval(timer);
                        $wire.finishQuiz();
                    } else { seconds--; }
                }, 1000);
                return () => clearInterval(timer)">
                    <span class="timer-label">Timer</span>
                    <span
                        x-text="String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0')"></span>
                </span>
            @else
                <span class="divider">Â·</span>
                <span class="quiz-timer"><span class="timer-label">Timer</span> No time limit</span>
            @endif
        </div>
    </div>

    <div class="question-nav px-4" aria-label="Question navigation">
        <div class="question-buttons">
            @foreach ($quiz->questions as $navQuestion)
                <button type="button"
                    class="question-number {{ array_key_exists($navQuestion->id, $answers) ? 'answered' : '' }} {{ $questionNumber === $loop->iteration ? 'current' : '' }}"
                    wire:key="question-nav-{{ $navQuestion->id }}" wire:click="updateCurrent({{ $loop->iteration }})"
                    @if ($questionNumber === $loop->iteration) aria-current="step" @endif>
                    {{ $loop->iteration }}
                </button>
            @endforeach
        </div>
        <span class="question-legend"><i class="legend-active"></i> Active
            <i class="legend-done"></i> Answered</span>
    </div>

    @if ($question)
        <section class="case-card" aria-labelledby="caseTitle">
            <div wire:key="question-card-{{ $question->id }}">
            <div class="case-header">
                <div>
                    <span class="eyebrow">CASE {{ str_pad($questionNumber, 2, '0', STR_PAD_LEFT) }} ·
                        {!! $topic !!}</span>
                    <h2 id="caseTitle">{{ $question->name ?: 'Clinical question' }}</h2>
                </div>
                <span class="difficulty-badge">DIFFICULTY ·
                    {{ strtoupper($question->difficulty ?: $quiz->difficulty ?: 'medium') }}</span>
            </div>

            <div class="question-meta" aria-label="Question details">
                <span><strong>TOPIC</strong> {!! $topic !!}</span>
                <span><strong>ELO GAIN</strong>
                    <span class="text-success">
                        +{{ $this->currentElo }}
                    </span>
                </span>
                <span><strong>ELO LOSS</strong>
                    <span class="text-danger">
                        -{{ $this->currentInCorrectElo }}
                    </span>
                </span>
            </div>

            <div class="vignette">
                <p>{!! $question->content !!}</p>
            </div>


            <div class="prompt">
                <span class="eyebrow">SELECT THE BEST ANSWER</span>
                <p>Choose the option that best answers this clinical question.</p>
            </div>

            <div class="quiz-options" role="radiogroup" aria-label="Diagnosis options">
                @foreach ($question->options as $option)
                    @php
                        $letter = $option->name ?: chr(64 + $loop->iteration);
                        $isSelected = (int) $answeredOptionId === (int) $option->id;
                    @endphp
                    <button class="quiz-option {{ $isSelected ? 'is-selected' : '' }}" type="button"
                        data-letter="{{ $letter }}" aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                        wire:key="question-{{ $question->id }}-option-{{ $option->id }}"
                        wire:click="submit({{ $option->id }}, {{ $question->id }})">
                        <span class="letter-badge">{{ $letter }}</span>
                        <span class="answer-text">{{ $option->content }}</span>
                        <span class="check-indicator"><i></i></span>
                    </button>
                @endforeach
            </div>
            </div>

            <div class="quiz-actions">
                <div class="keyboard-hint">
                    Choose an answer, then continue when ready.
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline-secondary" type="button" wire:click="previous"
                        @disabled($questionNumber === 1)>
                        <i class="bi bi-arrow-left"></i><span>Previous</span>
                    </button>

                    @if ($questionNumber < $questionCount)
                        <button class="btn btn-primary" type="button" wire:click="next">
                            <span>Next question</span><i class="bi bi-arrow-right"></i>
                        </button>
                    @else
                        <button class="btn btn-primary" type="button" wire:click="submitAttempt">
                            <span>Submit quiz</span><i class="bi bi-check"></i>
                        </button>
                    @endif
                </div>
            </div>

            @error('answers')
                <div class="alert alert-danger mt-3">Please answer {{ $this->count - count($answers) }} remaining
                    question(s).</div>
            @enderror
        </section>
    @else
        <section class="case-card" aria-labelledby="caseTitle">
            <div class="case-header">
                <div>
                    <span class="eyebrow">QUIZ UNAVAILABLE</span>
                    <h2 id="caseTitle">No question is available.</h2>
                </div>
            </div>
        </section>
    @endif
</div>
