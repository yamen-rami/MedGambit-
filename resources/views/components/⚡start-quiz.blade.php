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
            
            ->where('quiz_id', $this->quiz->id)
            ->first();
        
        $this->current = $this->attempt->current;
        $this->answers = $this->attempt->answers->pluck('option_id', 'question_id')->toArray();
        $this->loadQuestion();
    }

    #[On('quit-quiz')]
    public function quitQuiz()
    {
        if ($this->attempt?->status === 'finished') {
            return redirect()->route('quizResult', $this->quiz);
        }

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
        if ($this->attempt?->status === 'finished') {
            return;
        }

        $question = $this->currentQuestion;
        if (!$question) {
            abort(403);
        }
        if ((int) $question->id !== (int) $questionId || !$question->options->contains('id', (int) $optionId) || !$this->attempt) {
            return;
        }
        $question->loadMissing('correctAnswer');
        $this->attempt->answers()->updateOrCreate(['question_id' => $questionId], ['status' => 'answered', 'is_correct' => $question->correctAnswer?->id === (int) $optionId, 'option_id' => $optionId]);
        $this->answers[$questionId] = (int) $optionId;
    }

    public function next(): void
    {
        if ($this->current < $this->count) {
            $this->updateCurrent($this->current + 1);
        }
    }
    public function previous(): void
    {
        if ($this->current > 1) {
            $this->updateCurrent($this->current - 1);
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

    #[Computed]
    public function count(): int
    {
        return $this->quiz->questions->count();
    }
    #[Computed]
    public function remainingSeconds(): ?int
    {
        if ($this->attempt?->status === 'finished') {
            return null;
        }

        return $this->attempt?->finished_at ? max(0, (int) now()->diffInSeconds($this->attempt->finished_at, false)) : null;
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
        if ($this->attempt?->status === 'finished') {
            return redirect()->route('quizResult', $this->quiz);
        }

        app(QuizService::class)->updateAttempt(auth()->id(), $this->quiz->id, $this->answers);
        return redirect()->route('quizResult', $this->quiz);
    }
    public function submitAttempt()
    {
        $this->validate(['answers' => ['required', 'array', 'min:' . $this->count]]);
        if($this->attempt->status === "finished"){
            return ;
        }
        app(QuizService::class)->updateAttempt(auth()->id(), $this->quiz->id, $this->answers);
        return redirect()->route('quizResult', $this->quiz);
    }
};
?>
@php
    $question = $this->currentQuestion;
    $questionCount = (int) $this->count;
    $questionNumber = (int) $this->current;
    $answeredCount = count($answers);
    $remainingCount = max(0, $questionCount - $answeredCount);
    $answeredOptionId = $question ? $answers[$question->id] ?? null : null;
    $progress = $questionCount ? round(($answeredCount / $questionCount) * 100) : 0;
    $topic = $question?->topic ?: ($quiz->topic ?: 'Clinical medicine');
@endphp

<div class="quiz-arena" x-cloak x-data="{
    drawerOpen: false,
    toggleDrawer() { this.drawerOpen ? this.closeDrawer() : this.openDrawer() },
    openDrawer() {
        this.drawerOpen = true;
        this.$nextTick(() => {
            document.body.classList.add('quiz-drawer-open');
            this.$refs.drawer.focus()
        })
    },
    closeDrawer() {
        if (!this.drawerOpen) return;
        this.drawerOpen = false;
        document.body.classList.remove('quiz-drawer-open');
        this.$nextTick(() => this.$refs.trigger.focus())
    },
}" x-on:keydown.escape.window="closeDrawer()">
    <button class="quiz-mobile-bar quiz-mobile-trigger my-1" type="button" x-ref="trigger" @click="toggleDrawer()"
        aria-label="Open questions navigation"><i class="bi bi-list"></i><span>Questions </b></button>
    <div class="quiz-workspace">
        <div class="quiz-layout">
            <aside class="quiz-sidebar" aria-label="Quiz navigation">
                <div class="quiz-sidebar-header">
                    <div class="quiz-sidebar-kicker"><span>Quiz
                            progress</span><span>{{ $quiz->name ?: 'Medical quiz' }}</span></div>
                    <div class="quiz-sidebar-progress"><strong>Question <b>{{ $questionNumber }}</b> <small>of
                                {{ $questionCount }}</small></strong><span>{{ $progress }}% complete</span></div>
                    <div class="quiz-progress-track"><i style="width: {{ $progress }}%"></i></div>
                    <div class="quiz-sidebar-telemetry"><span>{{ $answeredCount }} answered · {{ $remainingCount }}
                            remaining</span><b class="gain">+{{ $this->currentElo }} ELO</b><b
                            class="loss">-{{ $this->currentInCorrectElo }} ELO</b></div>
                </div>
                <div class="quiz-question-list">
                    @foreach ($quiz->questions as $navQuestion)
                        @php($isCurrent = $questionNumber === $loop->iteration)
                        @php($isAnswered = array_key_exists($navQuestion->id, $answers))
                        <button type="button" wire:key="question-nav-{{ $navQuestion->id }}"
                            wire:click="updateCurrent({{ $loop->iteration }})"
                            class="quiz-question-row {{ $isCurrent ? 'current' : '' }} {{ $isAnswered ? 'answered' : '' }}"
                            @if ($isCurrent) aria-current="step" @endif>
                            <span
                                class="quiz-question-row-title"><b>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</b><span>{{ $navQuestion->name ?: strip_tags($navQuestion->topic ?: 'Clinical question') }}</span></span>
                            @if ($isAnswered)
                            <i class="bi bi-check-circle-fill"></i>@else<i class="empty"></i>
                            @endif
                        </button>
                    @endforeach
                </div>
                <div class="quiz-sidebar-footer"><span>Click any question to
                        jump</span><span>{{ $answeredCount }}/{{ $questionCount }} done</span></div>
            </aside>

            <main class="quiz-question-panel">
                @if ($question)
                    <header class="quiz-panel-header">
                        @if ($quiz->name)
                            <h1 class="quiz-name">{{ $quiz->name }}</h1>
                        @endif
                        <div class="quiz-tags"><b>Question {{ $questionNumber }} of
                                {{ $questionCount }}</b><span>{!! $topic !!}</span><span>{{ strtoupper($question->difficulty ?: $quiz->difficulty ?: 'medium') }}</span>
                        </div>
                        <div class="quiz-panel-status">
                            <b class="gain">+{{ $this->currentElo }} ELO</b>
                            <b class="text-danger ">-{{ $this->currentInCorrectElo }} ELO</b>
                            @if ($this->remainingSeconds !== null)
                                <span class="quiz-timer" wire:ignore x-data="{ seconds: {{ max(0, (int) $this->remainingSeconds) }} }" x-init="const timer = setInterval(() => {
                                    if (seconds <= 1) {
                                        clearInterval(timer);
                                        $wire.finishQuiz();
                                    } else { seconds--; }
                                }, 1000);
                                return () => clearInterval(timer)"><i
                                        class="bi bi-clock"></i><span>Timer</span><em
                                        x-text="String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0')"></em></span>
                            @else
                                <span class="quiz-timer"><i class="bi bi-clock"></i><span>Timer</span> No time
                                    limit</span>
                            @endif
                        </div>
                    </header>
                    <section class="quiz-panel-body" wire:key="question-card-{{ $question->id }}">
                        <div class="quiz-question-heading">
                            <div><small>{{ $question->name ?: 'Clinical question' }}</small>
                            </div><span class="text-danger">{{ $question->difficulty }}</span>
                        </div>
                        <article class="quiz-vignette">{!! $question->content !!}</article>
                        <div class="quiz-answer-list" role="radiogroup" aria-label="Answer options">
                            @foreach ($question->options as $option)
                                @php($letter = $option->name ?: chr(64 + $loop->iteration))
                                @php($isSelected = (int) $answeredOptionId === (int) $option->id)
                                <button type="button" @disabled($attempt->status === 'finished')
                                    wire:key="question-{{ $question->id }}-option-{{ $option->id }}"
                                    wire:click="submit({{ $option->id }}, {{ $question->id }})"
                                    class="quiz-answer-row {{ $isSelected ? 'selected' : '' }}"
                                    aria-checked="{{ $isSelected ? 'true' : 'false' }}"><b>{{ $letter }}</b><span>{{ $option->content }}</span><i><em></em></i></button>
                            @endforeach
                        </div>
                    </section>
                    <footer class="quiz-panel-footer"><span class="quiz-shortcut"><kbd>A–D</kbd> Select option</span>
                        <div><button class="quiz-button secondary" type="button" wire:click="previous"
                                @disabled($questionNumber === 1)><i class="bi bi-arrow-left"></i>Previous</button>
                            @if ($questionNumber < $questionCount)
                                <button class="quiz-button primary"  type="button" wire:click="next">Next question<i
                                    class="bi bi-arrow-right"></i></button>@else<button @disabled($attempt->status === "finished") class="quiz-button primary"
                                    type="button" wire:click="submitAttempt">Submit quiz<i
                                        class="bi bi-check-lg"></i></button>
                            @endif
                        </div>
                    </footer>
                    @error('answers')
                        <div class="quiz-submit-error">Please answer {{ $remainingCount }} remaining question(s).</div>
                    @enderror
                @else
                    <div class="quiz-empty"><i class="bi bi-clipboard-x"></i>
                        <h1>Quiz unavailable</h1>
                        <p>No question is available for this quiz.</p>
                    </div>
                @endif
            </main>
        </div>
    </div>
    <div class="quiz-drawer-backdrop" x-show="drawerOpen" x-cloak @click="closeDrawer()"></div>
    <aside class="quiz-mobile-drawer" x-show="drawerOpen" x-cloak x-transition x-ref="drawer" tabindex="-1"
        role="dialog" aria-modal="true" aria-label="Question navigation">
        <header>
            <div><strong>Quiz navigator</strong><span>Question {{ $questionNumber }} of {{ $questionCount }} ·
                    {{ $progress }}%</span></div><button type="button" @click="closeDrawer()"
                aria-label="Close questions"><i class="bi bi-x-lg"></i></button>
        </header>
        <div class="quiz-progress-track"><i style="width: {{ $progress }}%"></i></div>
        <p>{{ $answeredCount }} answered · {{ $remainingCount }} remaining</p>
        <div>
            @foreach ($quiz->questions as $navQuestion)
                @php($isCurrent = $questionNumber === $loop->iteration) @php($isAnswered = array_key_exists($navQuestion->id, $answers))
                <button type="button" wire:key="mobile-question-nav-{{ $navQuestion->id }}"
                    wire:click="updateCurrent({{ $loop->iteration }})" @click="closeDrawer()"
                    class="{{ $isCurrent ? 'current' : '' }} {{ $isAnswered ? 'answered' : '' }}"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        {{ $navQuestion->name ?: 'Clinical question' }}</span>
                    @if ($isAnswered)
                    <i class="bi bi-check-circle-fill"></i>@else
                        <i></i>
                    @endif
                </button>
            @endforeach
        </div>
    </aside>
</div>
