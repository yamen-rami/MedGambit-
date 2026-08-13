<?php

use App\Models\Option;
use App\Models\Questions;
use App\Models\QuizAttempt;
use App\Services\QuizService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Component;

new class extends Component {
  #[Session(key: 'quiz_id')]
  public $quiz_id;

  public $quiz;

  #[Session]
  public int $correctAnswers = 0;

  #[Session]
  public int $wrongAnswers = 0;

  #[Session]
  public $current = 1;

  #[Session]
  public array $answers = [];

  public $attempt;

  public function mount($quiz)
  {
    $this->quiz = $quiz->loadMissing(['questions.options', 'questions.correctAnswer']);
    if (session('quiz_id')) {
      if (session('quiz_id') !== $this->quiz->id) {
        $this->reset('current', 'answers', 'quiz_id', 'correctAnswers', 'wrongAnswers');
        session()->forget([
          'current',
          'answers',
          'quiz_id',
          'correctAnswers',
          'wrongAnswers',
        ]);
      }
    }
    $this->quiz_id = $this->quiz->id;
    $this->attempt = QuizAttempt::where('user_id', auth()->id())->where('quiz_id', $this->quiz->id)->first();
  }

  public function hydrate()
  {
    $this->quiz->loadMissing(['questions.options', 'questions.correctAnswer']);
  }

  public function submit($optionId, $questionId)
  {
    if (!array_key_exists($questionId, $this->answers)) {
      $this->answers[$questionId] = $optionId;
      $question = $this->quiz->questions->find($questionId);

      $this->answers[$questionId] = $optionId;

      if ($question->correctAnswer->id == $optionId) {
        $this->correctAnswers++;
      } else {
        $this->wrongAnswers++;
      }
    }

  }

  public function editCurrent($current)
  {
    $this->current = $current;
  }

  public function next()
  {
    if ($this->current < $this->quiz->questions->count()) {
      $this->current++;
    }
  }

  public function previous()
  {
    if ($this->current > 1) {
      $this->current--;
    }
  }

  public function updateCurrent($current)
  {
    $this->current = $current;
  }
  #[On('quit-quiz')]
  public function quitQuiz()
  {
    $questionsCount = $this->quiz->questions->count();
    $answers = $this->answers;
   
    $quizService = app(QuizService::class);
    $attempt = $quizService->updateAttempt(auth()->id(), $this->quiz->id, $answers);
    $this->reset('current', 'answers' , "wrongAnswers" , "correctAnswers" , "quiz_id");
    session()->forget([
      'current',
      'answers',
      'array',
    "correctAnswers", "quiz_id" , "wrongAnswers"
    ]);

    return redirect()->route('quizResult', $this->quiz);
  }
  public function submitAttempt()
  {
    $questionsCount = $this->quiz->questions->count();
    $answers = $this->answers;
    $this->validate([
      'answers' => ['required', 'array', "min:$questionsCount"],
    ]);
    $quizService = app(QuizService::class);

    $attempt = $quizService->updateAttempt(auth()->id(), $this->quiz->id, $answers);
    $this->reset('current', 'answers');

    session()->forget([
      'current',
      'answers',
      'array',
    ]);

    return redirect()->route('quizResult', $this->quiz);
  }
};
?>
<div class="">
  <x-slot:topic>
    Learning Mode
  </x-slot:topic>
  {{-- ===================== CONTENT ===================== --}}
  <div class="content-grid">
    {{-- ===================== CENTER ===================== --}}
    <section class="battle-col">
      {{-- ===================== VS CARD ===================== --}}

      {{-- ===================== QUESTIONS ===================== --}}
      @foreach ($quiz->questions as $question)
        @if ($loop->iteration === $current)
          <div class="question-card">
            {{-- QUESTION HEADER --}}
            <div class="question-head">
              <span class="question-index">
                Question {{ $loop->iteration }} / {{ $quiz->questions->count() }}
              </span>

              <span class="badge-medium"> {{ $quiz->difficulty ?? 'Medium' }} </span>
            </div>

            {{-- QUESTION --}}
            <p class="question-text">{{ $question->content }}</p>

            {{-- OPTIONS --}}
            <div class="options" x-data="{ showExplanation: false }">
              @php
                $correct = $question->correctAnswer->id;
              @endphp
              @foreach ($question->options as $option)
                  <div x-data="{ showEx: false }" class="option {{
                isset($answers[$question->id]) && $answers[$question->id] == $option->id
                ? $answers[$question->id] == $correct ? 'border-success' : 'border-danger' :
                ''
                                                    }}" x-cloak :class="showEx
                                                                                        ? @js(
                                                                                          isset($answers[$question->id])
                                                                                          ? ($option->id == $correct
                                                                                            ? 'border-success'
                                                                                            : 'border-danger')
                                                                                          : ''
                                                                                        )
                                                                                        : ''"
                    wire:click="submit({{ $option->id }}, {{ $question->id }})"
                    @click="((showEx = ! showEx), (showExplanation = true))">
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
                      <div x-show="showEx" style="">
                        <div>
                          <p class="d-block">{{ $option->explanation }}</p>
                        </div>
                      </div>
                    </div>
                    {{-- @dd($quiz->questions[5]) --}}
                  </div>

              @endforeach
            </div>

            {{-- QUESTION FOOT --}}
            <div class="question-foot">
              <button type="button" class="report-link">
                <i class="fa-regular fa-flag"></i>
                Report Question
              </button>

              {{-- TIMER --}}
            </div>
          </div>

          {{-- ===================== ACTIONS ===================== --}}
          <div class="actions-row">
            {{-- PREVIOUS --}}
            <button type="button" class="btn btn-ghost" wire:click="previous({{ $question->elo_correct }})"
              @disabled($loop->first)>
              <i class="fa-solid fa-chevron-left"></i>

              Previous
            </button>

            {{-- SKIP --}}
            {{-- <button type="button" class="btn btn-ghost btn-skip">

              <i class="fa-solid fa-bolt"></i>

              Skip

            </button> --}}

            {{-- NEXT / SUBMIT --}}
            @if (!$loop->last)
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
          <div class="question-card" x-show="showExplanation">
            <h6>Question Explanation :</h6>
            <p>{{ $question->main_explanation }}</p>
            <h6>Question High Yield</h6>
            <p>{{ $question->high_yield }}</p>
          </div>

        @endif

      @endforeach

      {{-- VALIDATION ERROR --}}
      @error('answers')
        <h1 class="text-danger fs-5 my-2 text-center">
          Please Add Answers Left Questions Answers = {{ $this->quiz->questions->count() - count($this->answers) }}
        </h1>

      @enderror
    </section>

    {{-- ===================== RIGHT SIDEBAR ===================== --}}
    <aside class="side-col">
      {{-- ===================== BATTLE STATUS ===================== --}}
      <div class="panel">
        <div class="panel-title-row">
          <span class="panel-title"> Quiz Info </span>

          <span class="live-pill">
            <span class="live-dot"></span>
            Live
          </span>
        </div>

        {{-- Battle Type --}}
        <div class="stat-row">
          <i class="fa-solid fa-swords stat-icon"></i>

          <div>
            <div class="stat-label">Question Count</div>

            <div class="stat-value">{{ $quiz->questions->count() }} Questions</div>
          </div>
        </div>

        {{-- Time --}}
        {{-- <div class="stat-row">
          <i class="fa-regular fa-clock stat-icon"></i>

          <div>
            <div class="stat-label">Time per Question</div>

            <div class="stat-value">
              {{-- Question --}}
              {{-- </div>
          </div>
        </div> --}}

        {{-- Reward --}}
        <div class="stat-row">
          <i class="fa-solid fa-trophy stat-icon"></i>

          <div>
            <div class="stat-label">Win Reward</div>

            <div class="stat-value">{{ $this->currentElo ?? 4 }}</div>
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

          <div class="progress-line-fill" style="
                                width:
                                {{
  $quiz->questions->count() > 1
  ? (($current - 1) / ($quiz->questions->count() - 1)) * 90
  : 0
                                }}%;
                            "></div>

          <div class="progress-dots">
            @foreach ($quiz->questions as $question)
              <button type="button" class="dot
                                                                                                                @if(isset($answers[$question->id]))
                                                                                                                  correct
                                                                                                                @elseif($current === $loop->iteration)
                                                                                                                  current
                                                                                                                @else
                                                                                                                  pending
                                                                                                                @endif
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

            <path id="gauge-arc" value="100" d="M 13 74 A 54 54 0 0 1 127 74" fill="none" class="gauge-arc"
              stroke-width="2" stroke-dasharray="{{ count($answers) * 5 }}" pathLength="100" stroke-linecap="round" />
          </svg>
          {{-- @dd(100 - count($answers)) --}}
          {{-- @dd(count($answers) * $quiz->questions->count()) --}}
          <div class="gauge-value">{{ count($answers) * (100 / count($quiz->questions)) }}%</div>

          <div class="gauge-label">Progress By Percentage </div>
        </div>

        <div class="perf-row">
          <div class="perf-item">
            <div class="perf-num perf-good">{{ $correctAnswers }}</div>

            <div class="perf-label">Correct</div>
          </div>
          <div class="perf-item">
            <div class="perf-num perf-bad">{{ $wrongAnswers }}</div>

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
    themeToggle.addEventListener('click', function () {
      const isDark = root.getAttribute('data-theme') === 'dark';

      root.setAttribute('data-theme', isDark ? 'light' : 'dark');

      themeToggle.innerHTML = isDark ? '<i class="fa-solid fa-moon"></i>' : '<i class="fa-solid fa-sun"></i>';
    });
  }
</script>