<?php

use App\Events\GameFinished;
use App\Events\playerAnswered;
use App\Models\Game;
use App\Models\GameAnswers;
use App\Models\Option;
use App\Models\Questions;
use App\Services\GameService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Component;

new class extends Component
{
    public $game = null;

    public $current;

    public $loading = true;

    public $progress = 0;

    public array $answers = [];

    public $attempt;

    public $attempts;

    public $gameId;

    public $player1;

    public $player2;

    public $currentPlayer;

    public $finished = false;

    public function mount($gameId)
    {
        $this->gameId = $gameId;
        $this->game = Game::find($gameId);
        if (! $this->game) {
            return;
        }
        $players = $this->game->players()->with('user')->get();

        $this->currentPlayer = $players->where('user_id', auth()->id())->first();
        // $this->attempts = $this->game->attempts()->with('answers')->get();
        if (! $this->currentPlayer) {
            abort(403, 'You are not a player in this game.');
        }

        if ($this->currentPlayer) {
            $this->current = $this->currentPlayer->current_question;
        }
        $this->attempt = $this->game->attempts->where('user_id', auth()->id())->first();
        if (! $this->attempt) {
            return;
        }
        if ($this->attempt->status == 'finished') {
            $this->finished = true;
        }
        $this->loading = $this->game->status !== 'playing';

        if (! $this->loading) {
            $this->game->loadMissing('questions.options', 'questions.correctAnswer');
        }
        if ($players->count() == 2) {
            $this->getProgress();

            if ($players->count() !== 2) {
                throw new RuntimeException('Game does not have exactly 2 players.');
            }

            $this->player1 = $players[0]->user;

            $this->player2 = $players[1]->user;
        }

        $this->answers = GameAnswers::where('game_attempt_id', $this->attempt->id)
            ->where('player_id', auth()->id())
            ->pluck('option_id', 'question_id')
            ->toArray();
    }

    public function hydrate()
    {
        $this->game->loadMissing('questions.options');
    }

    #[Computed]
    public function currentInCorrectElo()
    {
        return $this->quiz->questions[$this->current - 1]->elo_incorrect;
    }

    public function submit($optionId, $questionId)
    {
        if (! $this->attempt) {
            abort(403);
        }
        if ($this->game->status !== 'playing') {
            return;
        }
        if ($this->currentPlayer->status === 'finished') {
            return;
        }
        if (! $this->game) {
            return;
        }

        $question = $this->game
            ->questions()
            ->with(['correctAnswer', 'options'])
            ->findOrFail($questionId);
        if (! $question->options->contains('id', $optionId)) {
            return;
        }

        $isCorrect = $question->correctAnswer->id == $optionId;

        $this->attempt->answers()->updateOrCreate(
            [
                'question_id' => $questionId,
                'player_id' => auth()->id(),
            ],
            [
                'option_id' => $optionId,
                'is_correct' => $isCorrect,
            ],
        );

        $this->answers[$questionId] = $optionId;
        playerAnswered::dispatch(auth()->id(), $this->gameId);
    }

    public function editCurrent($current)
    {
        $this->current = $current;
    }

    public function next()
    {
        if ($this->current < $this->game->questions->count()) {
            $this->updateCurrent($this->current + 1);
        }
    }

    public function previous()
    {
        if ($this->current > 1) {
            $this->updateCurrent($this->current - 1);
        }
    }

    #[Computed]
    public function remainingSeconds()
    {
        if (! $this->game || ! $this->game->ended_at) {
            return;
        }

        return max(0, (int) now()->diffInSeconds($this->game->ended_at, false));
    }

    #[On('echo-private:game.{gameId},.game.started')]
    public function gameStarted($event)
    {
        $service = app(GameService::class);
        $this->game->loadMissing('questions.options', 'players');
        $players = $this->game->players()->with('user')->get();
        $service->gameStarted($this->game, $players);

        $this->currentPlayer = $players->where('user_id', auth()->id())->first();
        if (! $this->currentPlayer) {
            return;
        }
        $this->current = $this->currentPlayer->current_question;
        if ($players->count() > 1) {
            $this->player1 = $players[0]->user;
            $this->player2 = $players[1]->user;
        }
        $this->loading = false;
    }

    #[On('echo-private:playerAnswerd.{gameId},.game.progress')]
    public function getProgress()
    {
        $attempt = $this->game
            ->attempts()
            ->where('user_id', '!=', auth()->id())
            ->first();

        if (! $attempt) {
            return;
        }

        $this->progress = GameAnswers::where('game_attempt_id', $attempt->id)
            ->where('player_id', '!=', auth()->id())
            ->count();
    }

    public function updateCurrent($current)
    {
        if (! $this->game) {
            return;
        }
        $max = $this->game->questions->count();
        if ($current < 1 || $max < $current) {
            return;
        }
        $this->current = (int) $current;

        $this->currentPlayer->update([
            'current_question' => $current,
        ]);
    }

    #[Computed]
    public function currentElo()
    {
        if ($this->current > 1) {
            // Getting the current ELO WHERE NOT 1
            return $this->game->questions[$this->current - 1]->elo_correct;
        }
    }

    public function finishGame()
    {
        if (! $this->game) {
            return;
        }
        if (! $this->attempt) {
            return;
        }
        if ($this->game->status !== 'playing') {
            return;
        }
        if ($this->currentPlayer->status === 'finished') {
            return;
        }
        $player = $this->game->players->where('user_id', auth()->id())->first();
        if (! $player) {
            return;
        }
        $this->loading = false;

        $player->update([
            'status' => 'finished',
        ]);
        $this->finished = true;
        $service = app(GameService::class);

        $service->editAttempt($this->attempt, $this->game);

        if ($this->game->finishedPlayers() === 2) {
            GameFinished::dispatch($this->game->id);
        }
    }

    public function submitAttempt()
    {
        $game = Game::find($this->gameId);
        if (! $game) {
            return;
        }
        $length = $game->questions->count();
        $answersCount = $this->attempt
            ->answers()
            ->where('player_id', auth()->id())
            ->count();

        if ($answersCount !== $length) {
            $this->addError('answers', 'Please answer all questions.');

            return;
        }
        if (! $this->attempt) {
            return;
        }
        if ($game->status !== 'playing') {
            return;
        }

        if ($this->currentPlayer->status === 'finished') {
            return;
        }

        $player = $game->players->where('user_id', auth()->id())->first();
        if (! $player) {
            return;
        }
        $this->loading = false;

        $player->update([
            'status' => 'finished',
        ]);
        $this->finished = true;
        $service = app(GameService::class);

        $service->editAttempt($this->attempt, $this->game);

        if ($game->finishedPlayers() === 2) {
            GameFinished::dispatch($game->id);
        }
    }

    #[On('echo-private:game.finished.{gameId},.game.finished')]
    public function toResults()
    {
        if (! $this->game) {
            return;
        }
        if (! $this->currentPlayer) {
            return;
        }
        $service = app(GameService::class);
        $service->finishGame($this->game->attempts);

        return redirect()->route('game.results', [
            'game' => $this->game,
        ]);
    }
};
?>
<div>
    @if ($this->loading)
        @push('style')
            <link rel="stylesheet" href="{{ asset('assets/css/shimmer.css') }}" />
        @endpush
        <div class="skeleton-wrap">
            <div class="grid">
                <div class="card main">
                    <div class="players-grid my-5">
                        <div class="player-card-skeleton">
                            <div class="player-avatar skeleton"></div>
                            <div class="player-name skeleton">
                                <div class="text-white"></div>
                            </div>
                            <div class="player-score skeleton"></div>
                        </div>
                        <div class="vs-text skeleton">VS</div>
                        <div class="player-card-skeleton">
                            <div class="player-avatar skeleton"></div>
                            <div class="player-name skeleton"></div>
                            <div class="player-score skeleton"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="skeleton h-14 w-90"></div>
                        <div class="skeleton pill h-22 w-50"></div>
                    </div>
                    <div class="skeleton w-70p mt-20 mb-20 h-20"></div>
                    <div class="options">
                        <div class="skeleton r8 h-44"></div>
                        <div class="skeleton r8 h-44"></div>
                        <div class="skeleton r8 h-44"></div>
                        <div class="skeleton r8 h-44"></div>
                    </div>
                    <div class="row">
                        <div class="skeleton h-14 w-100"></div>
                        <div class="skeleton h-14 w-60"></div>
                    </div>
                    <div class="row mt-20">
                        <div class="skeleton w-40p r8 mr-1 h-38"></div>
                        <div class="skeleton w-40p r8 h-38"></div>
                    </div>
                </div>

                <div class="side">
                    <div class="card side-card">
                        <div class="skeleton mb-14 h-14 w-100"></div>
                        <div class="skeleton w-80p mb-8 h-12"></div>
                        <div class="skeleton w-60p mb-14 h-16"></div>
                        <div class="skeleton w-80p mb-8 h-12"></div>
                        <div class="skeleton w-40p h-16"></div>
                    </div>

                    <div class="card side-card">
                        <div class="skeleton mb-14 h-14 w-110"></div>
                        <div class="dots">
                            <div class="skeleton r6 h-26"></div>
                            <div class="skeleton r6 h-26"></div>
                            <div class="skeleton r6 h-26"></div>
                            <div class="skeleton r6 h-26"></div>
                            <div class="skeleton r6 h-26"></div>
                        </div>
                    </div>

                    <div class="card side-card center">
                        <div class="skeleton center-x mb-16 h-14 w-90"></div>
                        <div class="skeleton circle center-x mb-10"></div>
                        <div class="skeleton center-x h-12 w-60"></div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- ===================== CONTENT ===================== --}}
        <div class="content-grid">
            {{-- ===================== CENTER ===================== --}}
            <section class="battle-col">
                <div class="alert alert-danger bg-danger border border-0 text-white" role="alert" wire:offline>
                    You Are Offline
                </div>
                {{-- ===================== VS CARD ===================== --}}
                <div class="vs-card">
                    <div class="vs-top">
                        <div class="player">
                            <div class="avatar avatar-blue lg">R</div>
                            <div>
                                <div class="">{{ auth()->user()->name }}</div>
                                <div class="player-elo">
                                    ELO {{ auth()->user()->game_rank }} <i class="fa-solid fa-trophy"></i>
                                </div>
                            </div>
                        </div>

                        <div class="score-mid">
                            <span class="vs-pill">VS</span>
                        </div>

                        <div class="player player-right">
                            <div>
                                <div>
                                    {{ $this->player1?->id === auth()->id() ? $this->player2?->name : $this->player1?->name }}
                                </div>

                                <div class="player-elo right">
                                    ELO {{ $this->player1?->id === auth()->id() ? $this->player2?->game_rank : $this->player1?->game_rank }}
                                    <i class="fa-solid fa-trophy"></i>
                                </div>
                            </div>

                            <div class="avatar avatar-peach lg">O</div>
                        </div>
                    </div>
                    {{-- TODO Vs  --}}
                    <div class="dual-bar" id="dual-bar">
                        <div class="dual-bar-blue"></div>
                        <div class="dual-bar-red"></div>
                    </div>
                </div>
                {{-- ===================== QUESTIONS ===================== --}}
                @foreach ($game->questions as $question)
                    @if ($loop->iteration === $current)
                        <div class="question-card">
                            {{-- QUESTION HEADER --}}
                            <div class="question-head">
                                <span class="question-index">
                                    Question {{ $loop->iteration }} / {{ $game->questions->count() }}
                                </span>
                                @foreach ($game?->difficulty ?? [] as $d)
                                    <span class="badge-medium"> {{ $d }} </span>
                                @endforeach
                            </div>

                            {{-- QUESTION --}}
                            <p class="question-text">{{ $question->content }}</p>

                            {{-- OPTIONS --}}
                            <div class="options">
                                @foreach ($question->options as $option)
                                    <div
                                        class="option
                                                                                                                                                                                                                                                                                                                                                                                                                            {{ isset($answers[$question->id]) && $answers[$question->id] == $option->id ? 'selected' : '' }}"
                                        wire:click="submit({{ $option->id }}, {{ $question->id }})"
                                    >
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
                                @if ($this->remainingSeconds !== null)
                                    <div
                                        class="timer"
                                        x-data="{
                                        seconds: {{ $this->remainingSeconds ?? 0 }},
                                        timer: null,
                                    
                                        get minutes() {
                                            return Math.floor(this.seconds / 60)
                                        },
                                    
                                        get displaySeconds() {
                                            return this.seconds % 60
                                        },
                                    
                                        start() {
                                    
                                            this.timer = setInterval(() => {
                                    
                                                this.seconds--
                                    
                                                if (this.seconds <= 0) {
                                    
                                                    clearInterval(this.timer)
                                    
                                                    $wire.finishGame()
                                    
                                                }
                                    
                                            }, 1000)
                                    
                                        }
                                    }"
                                        x-init="start()"
                                    >
                                        <i class="fa-regular fa-clock"></i>

                                        <span x-text="minutes"></span>

                                        <span>:</span>

                                        <span x-text="String(displaySeconds).padStart(2, '0')"></span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- ===================== ACTIONS ===================== --}}
                        <div class="actions-row">
                            {{-- PREVIOUS --}}
                            <button type="button" class="btn btn-ghost" wire:click="previous" @disabled($loop->first)>
                                <i class="fa-solid fa-chevron-left"></i>

                                Previous
                            </button>

                            {{-- SKIP --}}
                            {{-- <button type="button" class="btn btn-ghost btn-skip">

                                <i class="fa-solid fa-bolt"></i>

                                Skip

                            </button> --}}

                            {{-- NEXT / SUBMIT --}}
                            @if (! $loop->last)
                                <button type="button" class="btn btn-primary" wire:click="next()">
                                    Next
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    wire:click="submitAttempt({{ $this->game }})"
                                >
                                    Submit

                                    <i class="fa-solid fa-check"></i>
                                </button>
                            @endif
                        </div>
                    @endif
                @endforeach

                {{-- VALIDATION ERROR --}}
                @error('answers')
                    <h1 class="text-danger fs-5 my-2 text-center">
                        Please Add Answers Left Questions Answers = {{ $this->game->questions->count() - count($this->answers) }}
                    </h1>
                @enderror
            </section>

            {{-- ===================== RIGHT SIDEBAR ===================== --}}
            <aside class="side-col">
                {{-- ===================== BATTLE STATUS ===================== --}}
                <div class="panel">
                    <div
                        class="panel-title-row"
                        x-data="{ online: navigator.onLine }"
                        x-init="
                            window.addEventListener('online', () => (online = true));
                            window.addEventListener('offline', () => (online = false));
                        "
                    >
                        <span class="panel-title">Battle Status</span>

                        <span class="live-pill">
                            <template x-if="online">
                                <span class="flex items-center gap-2">
                                    <span class="live-dot"></span>
                                    <span>Live</span>
                                </span>
                            </template>

                            <template x-if="! online">
                                <span class="flex items-center gap-2">
                                    <span class="live-dot-offline"></span>
                                    <span class="text-danger">Offline</span>
                                </span>
                            </template>
                        </span>
                    </div>

                    {{-- Battle Type --}}
                    <div class="stat-row">
                        <svg
                            class="menu-icon icon-base text-primary"
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-circle-question-mark-icon lucide-circle-question-mark"
                        >
                            <circle cx="12" cy="12" r="10" />
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                            <path d="M12 17h.01" />
                        </svg>

                        <div>
                            <div class="stat-label">Battle Type</div>

                            <div class="stat-value">{{ $game->questions->count() }} Questions</div>
                        </div>
                    </div>

                    {{-- Reward --}}
                    <div class="stat-row">
                        <i class="fa-solid fa-trophy stat-icon"></i>

                        <div>
                            <div class="stat-label">Win Reward</div>

                            <div class="stat-value">{{ $this->currentElo ?? 4 }}</div>
                        </div>
                    </div>
                    <div class="stat-row">
                        <i class="fa-solid fa-trophy stat-icon"></i>

                        <div>
                            <div class="stat-label">Lose Reward</div>

                            <div class="stat-value">{{ $this->currentInCorrectElo ?? 5 }}</div>
                        </div>
                    </div>
                </div>

                {{-- ===================== BATTLE PROGRESS ===================== --}}
                <div class="panel" wire:show="!finished">
                    <div class="panel-title-row">
                        <span class="panel-title"> Battle Progress </span>

                        <span class="progress-frac"> {{ $current }} / {{ $game->questions->count() }} </span>
                    </div>

                    <div class="progress-track">
                        <div class="progress-line-bg"></div>

                        <div
                            class="progress-line-fill"
                            style="
                                                            width:
                                                            {{ $game->questions->count() > 1 ? (($current - 1) / ($game->questions->count() - 1)) * 90 : 0 }}%;
                                                        "
                        ></div>

                        <div class="progress-dots">
                            @foreach ($game->questions as $question)
                                <button
                                    type="button"
                                    class="dot
                                                                                                                                                                @if (isset($answers[$question->id])) correct
                                                                                                                                                                @elseif($current === $loop->iteration)
                                                                                                                                                                      current
                                                                                                                                                                @else
                                                                                                                                                                      pending @endif
                                                                                                                                                            "
                                    wire:click="updateCurrent({{ $loop->iteration }})"
                                >
                                    {{ $loop->iteration }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="panel justify-content-center align-items-center" wire:show="finished">
                    Waiting For Your Opponent
                </div>

                {{-- ===================== PERFORMANCE ===================== --}}
                <div class="panel center-panel">
                    <div class="panel-title">Opponent Progress</div>

                    <div class="gauge-wrap">
                        <svg viewBox="0 0 140 80" width="150" height="88">
                            <path
                                d="M 13 74 A 54 54 0 0 1 127 74"
                                fill="none"
                                class="gauge-bg"
                                stroke-width="3"
                                stroke-linecap="round"
                            />

                            <path
                                id="gauge-arc"
                                d="M 13 74 A 54 54 0 0 1 127 74"
                                fill="none"
                                class="gauge-arc"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-dasharray="{{ count($answers) != 0 ? $this->progress * (100 / count($game->questions)) : 0 }}"
                                pathLength="100"
                            />
                        </svg>

                        <div class="gauge-value">
                            <span class="text-primary"> {{ $this->progress }} </span>
                            / {{ $game->questions->count() }}
                        </div>

                        <div class="gauge-label">Progress</div>
                    </div>
                </div>
        </div>

        {{-- ===================== TOPIC ===================== --}}
        <div class="panel">
            <div class="panel-title">{{ $this->progress }}</div>

            <div class="topic-row">
                <div class="topic-icon">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>

                <div>
                    <div class="topic-name">{{ $game->topic ?? 'Cardiology' }}</div>
                </div>
            </div>
        </div>
        </aside>

        {{-- ===================== FOOTER ===================== --}}
        <footer class="footer">
            <i class="fa-solid fa-shield-halved"></i>
            Every question is a battle. Every battle makes you better.
        </footer>

    @endif
    @script
        <script>
            const themeToggle = document.getElementById('theme-toggle');
            const root = document.documentElement;

            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    const isDark = root.getAttribute('data-theme') === 'dark';

                    root.setAttribute('data-theme', isDark ? 'light' : 'dark');

                    themeToggle.innerHTML = isDark
                        ? '<i class="fa-solid fa-moon"></i>'
                        : '<i class="fa-solid fa-sun"></i>';
                });
            }
        </script>
    @endscript
</div>

{{-- ===================== DARK MODE ONLY ===================== --}}
