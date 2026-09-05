<?php

use App\Models\Questions;
use App\Models\QuizAttempt;
use App\Services\QuizService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $quiz;
    public int $correctCount = 0;
    public int $wrongCount = 0;
    public $currentQuestion;
    public int $current = 1;
    public $activeOptionId = null; // Changed to null for strict comparison
    public array $answers = [];
    public $attempt;

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

        $this->loadQuestion();
    }

    public function loadQuestion()
    {
        $questionId = $this->quiz->questions->get($this->current - 1)?->id;
        if (!$questionId) {
            return;
        }

        $this->currentQuestion = Questions::with('options', 'correctAnswer', 'playedCount')->findOrFail($questionId);

        // If already answered, automatically open the explanation for the chosen option
        $this->activeOptionId = $this->answers[$questionId] ?? null;
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
        // Don't allow changing an already answered question
        if (array_key_exists($questionId, $this->answers)) {
            return;
        }

        $question = $this->currentQuestion;
        if (!$question || !$question->options->contains('id', $optionId) || !$this->attempt) {
            return;
        }

        $question->loadMissing('correctAnswer');
        $isCorrect = $question->correctAnswer?->id === (int) $optionId;

        if ($isCorrect) {
            $this->correctCount++;
            $this->attempt->increment('correctCount');
        } else {
            $this->wrongCount++;
            $this->attempt->increment('wrongCount');
        }

        $this->attempt->answers()->updateOrCreate(
            ['question_id' => $questionId],
            [
                'status' => 'answered',
                'is_correct' => $isCorrect,
                'option_id' => $optionId,
            ],
        );

        // Open the explanation for the selected option
        $this->answers[$questionId] = (int) $optionId;
    }

    public function next()
    {
        if ($this->current < $this->quiz->questions->count()) {
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
        $max = $this->quiz->questions->count();
        if ($current < 1 || $max < $current) {
            return;
        }

        $this->current = (int) $current;
        $this->attempt->current = (int) $current;
        $this->attempt->save();

        // loadQuestion will handle resetting/setting activeOptionId appropriately
        $this->loadQuestion();
    }

    #[On('quit-quiz')]
    public function quitQuiz()
    {
        $quizService = app(QuizService::class);
        $quizService->updateAttempt(auth()->id(), $this->quiz->id, $this->answers);

        return redirect()->route('quizResult', $this->quiz);
    }

    public function submitAttempt()
    {
        $questionsCount = $this->quiz->questions->count();

        $this->validate([
            'answers' => ['required', 'array', "min:$questionsCount"],
        ]);

        $quizService = app(QuizService::class);
        $quizService->updateAttempt(auth()->id(), $this->quiz->id, $this->answers);

        return redirect()->route('quizResult', $this->quiz);
    }
};
?>
<div class="">
    <x-slot:topic>Learning Mode</x-slot:topic>
    {{-- ===================== CONTENT ===================== --}}
    <div class="content-grid">
        {{-- ===================== CENTER ===================== --}}
        <section class="battle-col">
            @php
                $question = $this->currentQuestion;
            @endphp

            <div class="question-card">
                {{-- QUESTION HEADER --}}
                <div class="question-head">
                    <span class="question-index">
                        Question {{ $this->current }} / {{ $quiz->questions->count() }}
                    </span>

                    <span class="badge-medium"> {{ $quiz->difficulty ?? 'Medium' }} </span>
                </div>

                {{-- QUESTION --}}
                <p class="question-text">{{ $question->content }}</p>

                {{-- OPTIONS --}}
                <div class="options" x-data="{ activeOptionId: $wire.activeOptionId }">
                    @php
                        $correct = $question->correctAnswer->id;
                    @endphp
                    @foreach ($question->options as $option)
                        @php
                            // Calculate the border class based on whether the question has been answered
                            $borderClass = isset($answers[$question->id])
                                ? ($option->id == $correct
                                    ? 'border-success'
                                    : 'border-danger')
                                : '';
                        @endphp

                        <div class="option" x-cloak {{-- Apply border only if the question has been answered --}} :class="'{{ $borderClass }}'"
                            wire:click="submit({{ $option->id }}, {{ $question->id }})"
                            @click="$wire.set('activeOptionId', $wire.activeOptionId == {{ $option->id }} ? null : {{ $option->id }})">

                            <span class="option-key">
                                @if ($loop->iteration === 1)
                                    A
                                @elseif ($loop->iteration === 2)
                                    B
                                @elseif ($loop->iteration === 3)
                                    C
                                @elseif ($loop->iteration === 4)
                                    D
                                @else
                                    E
                                @endif
                            </span>

                            <span class="option-label"> {{ $option->content }} </span>

                            <span class="option-check">
                                <i class="fa-solid fa-check"></i>
                            </span>

                            <div></div>

                            <div class="d-flex">
                                {{-- Only show if this specific option's ID matches the activeOptionId --}}
                                <div x-show="$wire.activeOptionId == {{ $option->id }}" x-collapse>
                                    <div>
                                        <p class="d-block">{{ $option->explanation }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Todo <div class="question-foot">
                    <button type="button" class="report-link">
                        <i class="fa-regular fa-flag"></i>
                        Report Question
                    </button>
                </div> --}}
            </div>

            {{-- ===================== ACTIONS ===================== --}}
            <div class="actions-row">
                <button type="button" class="btn btn-ghost" wire:click="previous({{ $question->elo_correct }})"
                    @disabled($this->current < 1)>
                    <i class="fa-solid fa-chevron-left"></i>
                    Previous
                </button>

                {{-- SKIP --}}
                {{-- <button type="button" class="btn btn-ghost btn-skip">

              <i class="fa-solid fa-bolt"></i>

              Skip

            </button> --}}

                {{-- NEXT / SUBMIT --}}
                @if ($this->current !== $this->quiz->questions->count())
                    <button type="button" class="btn btn-primary" wire:click="next({{ $question->elo_correct }})">
                        Next

                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                @else
                    <button type="button" class="btn btn-primary" wire:click="submitAttempt">
                        Submit

                        <i class="fa-solid fa-check"></i>
                    </button>
                @endif
            </div>
            <div class="question-card" x-show="$wire.activeOptionId">
                <h6>Question Explanation :</h6>
                <p>{{ $question->main_explanation }}</p>
                <h6>Question High Yield</h6>
                <p>{{ $question->high_yield }}</p>
            </div>

            {{-- VALIDATION ERROR --}}
            @error('answers')
                <h1 class="text-danger fs-5 my-2 text-center">
                    Please Add Answers Left Questions Answers =
                    {{ $this->quiz->questions->count() - count($this->answers) }}
                </h1>
            @enderror
        </section>

        {{-- ===================== RIGHT SIDEBAR ===================== --}}
        <aside class="side-col">
            <div class="panel">


                <div class="stat-row">
                    <svg class="text-warning" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-refresh-ccw">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                        <path d="M3 3v5h5" />
                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                        <path d="M16 16h5v5" />
                    </svg>
                    <div>
                        <div class="stat-label fw-bold fs-6" style="color: var(--text)">Frequency</div>

                        <div class="stat-value">{{ $question->playedCount?->count ?? 0 }} </div>
                    </div>
                </div>



                <div class="stat-row p-0">
                    <i class="fa-solid fa-trophy stat-icon"></i>

                    <div>
                        <div class="stat-label fw-bold fs-6" style="color: var(--text)" >Win Elo</div>

                        <div class="stat-value">{{ $this->currentElo ?? 4 }}</div>
                    </div>
                </div>
                <div class="stat-row p-0">
                    <div class="stat-row">
                        <svg class="text-opacity-10 text-danger" xmlns="http://www.w3.org/2000/svg" width="20"
                            height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down">
                            <path d="M12 5v14" />
                            <path d="m19 12-7 7-7-7" />
                        </svg>
                        <div>
                            <div class="stat-label fw-bold fs-6" style="color: var(--text)">Losing Elo</div>
                            <div class="stat-value fw-bold fs-6">{{ $this->currentInCorrectElo ?? 5 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================== BATTLE PROGRESS ===================== --}}
            <div class="panel">
                <div class="panel-title-row">
                    <span class="panel-title"> Quiz Progress </span>

                    <span class="progress-frac"> {{ $current }} / {{ $quiz->questions->count() }} </span>
                </div>

                <div class="progress-track">
                    <div class="progress-line-bg"></div>

                    <div class="progress-line-fill"
                        style="
                                width:
                                {{ $quiz->questions->count() > 1 ? (($current - 1) / ($quiz->questions->count() - 1)) * 90 : 0 }}%;
                            ">
                    </div>

                    <div class="progress-dots">
                        @foreach ($quiz->questions as $question)
                            <button type="button"
                                class="dot
                                                                                                                  @if (isset($answers[$question->id])) correct
                                                                                                                  @elseif($current === $loop->iteration)
                                                                                                                    current
                                                                                                                  @else
                                                                                                                    pending @endif
                                                                                                              "
                                wire:click="updateCurrent({{ $loop->iteration }})">
                                {{ $loop->iteration }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ===================== PERFORMANCE ===================== --}}
            <div class="panel center-panel">
                <div class="panel-title">Your Performance</div>

                <div class="gauge-wrap">
                    <svg viewBox="0 0 140 80" width="150" height="88">
                        <path d="M 13 74 A 54 54 0 0 1 127 74" fill="none" class="gauge-bg" stroke-width="2"
                            stroke-linecap="round" />

                        <path id="gauge-arc" value="100" d="M 13 74 A 54 54 0 0 1 127 74" fill="none"
                            class="gauge-arc" stroke-width="2" stroke-dasharray="{{ count($answers) * 5 }}"
                            pathLength="100" stroke-linecap="round" />
                    </svg>
                    {{-- @dd(100 - count($answers)) --}}
                    {{-- @dd(count($answers) * $quiz->questions->count()) --}}
                    @php
                        $questionsCount = $quiz->questions->count();
                        $answeredCount = count($answers);

                        $progress = $questionsCount > 0 ? ($answeredCount / $questionsCount) * 100 : 0;
                    @endphp

                    <div class="gauge-value">{{ round($progress) }}%</div>

                    <div class="gauge-label">Progress By Percentage</div>
                </div>

                <div class="perf-row">
                    <div class="perf-item">
                        <div class="perf-num perf-good">{{ $correctCount }}</div>

                        <div class="perf-label">Correct</div>
                    </div>
                    <div class="perf-item">
                        <div class="perf-num perf-bad">{{ $wrongCount }}</div>

                        <div class="perf-label">Incorrect</div>
                    </div>
                </div>
            </div>

            {{-- ===================== TOPIC ===================== --}}
            <div class="panel">
                <div class="panel-title">Topic</div>

                <div class="topic-row">
                    <div class="topic-icon">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>

                    <div>
                        <div class="topic-name">{{ $quiz->topic ?? 'Cardiology' }}</div>

                        {{-- <div class="topic-sub">Myocardial Infarction</div> --}}
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="footer">
        <i class="fa-solid fa-shield-halved"></i>

        Every question is a battle. Every battle makes you better.
    </footer>
</div>

{{-- ===================== DARK MODE ONLY ===================== --}}
<script>
    const themeToggle = document.getElementById('theme-toggle');
    const root = document.documentElement;

    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const isDark = root.getAttribute('data-theme') === 'dark';

            root.setAttribute('data-theme', isDark ? 'light' : 'dark');

            themeToggle.innerHTML = isDark ? '<i class="fa-solid fa-moon"></i>' :
                '<i class="fa-solid fa-sun"></i>';
        });
    }
</script>
