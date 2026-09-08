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

new class extends Component {
    public $game = null;

    public $current;

    public $loading = true;

    public $progress = 0;

    public array $answers = [];

    public $attempt;

    public $attempts;
    public $currentQuestion;
    public $gameId;

    public $player1;
    public $playersCount = 0  ; 
    public $player2;

    public $currentPlayer;

    public $finished = false;

    public function mount($gameId)
    {
        $this->gameId = $gameId;
        $this->game = Game::find($gameId);
        if (!$this->game) {
            return;
        }
        $players = $this->game->players()->with('user')->get();
        $this->currentPlayer = $players->where('user_id', auth()->id())->first();
        if (!$this->currentPlayer) {
            abort(403, 'You are not a player in this game.');
        }

        if ($this->currentPlayer) {
            $this->current = $this->currentPlayer->current_question;
        }
        $this->attempt = $this->game->attempts->where('user_id', auth()->id())->first();
        if (!$this->attempt) {
            return;
        }
        if ($this->attempt->status == 'finished') {
            $this->finished = true;
        }
        $this->loading = $this->game->status !== 'playing';

        if (!$this->loading) {
            $this->loadQuestion();
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
        $this->loadQuestion();
    }
    #[Computed]
    public function count()
    {
        return $this->game->questions->count();
    }

    public function loadQuestion()
    {
        $questionId = $this->game->questions->get($this->current - 1)?->id;
        if (!$questionId) {
            return;
        }
        $this->currentQuestion = Questions::with('options', 'correctAnswer', 'playedCount')->findOrFail($questionId);
        if (!$this->currentQuestion) {
            abort(403, 'something went wrong');
        }
    }

    #[Computed]
    public function currentInCorrectElo()
    {
        return $this->currentQuestion->elo_incorrect;
    }

    public function submit($optionId, $questionId)
    {
        if (!$this->attempt) {
            abort(403);
        }
        if ($this->game->status !== 'playing') {
            return;
        }
        if ($this->currentPlayer->status === 'finished') {
            return;
        }
        if (!$this->game) {
            return;
        }

        $question = $this->game
            ->questions()
            ->with(['correctAnswer', 'options'])
            ->findOrFail($questionId);
        if (!$question->options->contains('id', $optionId)) {
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
        if (!$this->game || !$this->game->ended_at) {
            return;
        }

        return max(0, (int) now()->diffInSeconds($this->game->ended_at, false));
    }

    #[On('echo-private:game.{gameId},.game.started')]
    public function gameStarted($event)
    {
        $service = app(GameService::class);
        $this->loadQuestion();
        $this->game->loadMissing('players');
        $players = $this->game->players()->with('user')->get();
        $service->gameStarted($this->game, $players);

        $this->currentPlayer = $players->where('user_id', auth()->id())->first();
        if (!$this->currentPlayer) {
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

        if (!$attempt) {
            return;
        }

        $this->progress = GameAnswers::where('game_attempt_id', $attempt->id)
            ->where('player_id', '!=', auth()->id())
            ->count();
    }

    public function updateCurrent($current)
    {
        if (!$this->game) {
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
        $this->loadQuestion();
    }

    #[Computed]
    public function currentElo()
    {
        return $this->currentQuestion->elo_correct;
    }

    public function finishGame()
    {
        if (!$this->game) {
            return;
        }
        if (!$this->attempt) {
            return;
        }
        if ($this->game->status !== 'playing') {
            return;
        }
        if ($this->currentPlayer->status === 'finished') {
            return;
        }
        $player = $this->game->players->where('user_id', auth()->id())->first();
        if (!$player) {
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
        if (!$game) {
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
        if (!$this->attempt) {
            return;
        }
        if ($game->status !== 'playing') {
            return;
        }

        if ($this->currentPlayer->status === 'finished') {
            return;
        }

        $player = $game->players->where('user_id', auth()->id())->first();
        if (!$player) {
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
        if (!$this->game) {
            return;
        }
        if (!$this->currentPlayer) {
            return;
        }
        $service = app(GameService::class);
        $service->finishGame($this->game->attempts);

        return redirect()->route('game.results', [
            'game' => $this->game,
        ]);
    }
    #[On('quit-quiz')]
    public function quitGame()
    {
        if (!$this->game) {
            return;
        }
        if (!$this->attempt) {
            return;
        }
        if ($this->game->status !== 'playing') {
            return;
        }
        if ($this->currentPlayer->status === 'finished') {
            return;
        }
        $player = $this->game->players->where('user_id', auth()->id())->first();
        if (!$player) {
            return;
        }
        $this->loading = false;
        $player->update([
            'status' => 'finished',
        ]);
        $this->finished = true;
        $service = app(GameService::class);
        $service->editAttempt($this->attempt, $this->game);
        GameFinished::dispatch($this->game->id);
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
                        <div class="vs-text skeleton text-center">
                            <span style="color: var(--text)"
                                class="
  
                            rounded-circle px-3 py-3 text-center skeleton">VS</span>
                        </div>
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
        @if ($this->game->challenge_token)
            {{-- @dd($this->game->players()->get()) --}}
            @if ($this->game->challenge_token)
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-3">
                    <div class="mx-auto rounded-3 shadow-lg p-4 text-center"
                        style="max-width: 450px; background: var(--bg); color: var(--text);" x-data="{
                            copied: false,
                            link: window.location.origin + '/game/friend/{{ $game->challenge_token }}',
                            copy() {
                                const temp = document.createElement('textarea');
                                temp.value = this.link;
                                temp.style.position = 'fixed';
                                temp.style.opacity = '0';
                                document.body.appendChild(temp);
                                temp.focus();
                                temp.select();
                                try {
                                    document.execCommand('copy');
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 2000);
                                } catch (e) {
                                    console.error('Copy failed', e);
                                }
                                document.body.removeChild(temp);
                            }
                        }">
                        <h4 class="mb-2">Invite a Friend</h4>

                        <p class="mb-4 opacity-75">
                            Share this challenge token with your friend to join the game.
                        </p>

                        <div class="rounded-3 p-3 mb-3" style="background: var(--text); color: var(--bg);">
                            <small class="d-block mb-2 opacity-75">Challenge Token</small>

                            <div class="d-flex align-items-center gap-2">
                                <div class="flex-grow-1 font-monospace fw-semibold text-truncate"
                                    style="color: var(--bg);" x-text="link"></div>

                                <button type="button" class="btn btn-sm"
                                    style="background: var(--bg); color: var(--text); min-width: 70px;"
                                    x-on:click="copy()">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak>✓ Copied!</span>
                                </button>
                            </div>
                        </div>

                        <div class="small opacity-75">
                            <i class="ti ti-loader-2 me-1"></i>
                            Waiting for your friend to join...
                        </div>
                    </div>
                </div>
            @endif
        @endif
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
                            <div class="avatar avatar-blue lg">
                                {{ Str::upper(Str::limit(auth()->user()->name, 1, '')) }}</div>
                            <div>
                                <div class="">{{ auth()->user()->name }}</div>
                                <div class="player-elo">
                                    ELO {{ auth()->user()->rank }} <i class="fa-solid fa-trophy"></i>
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
                                    ELO
                                    {{ $this->player1?->id === auth()->id() ? $this->player2?->rank : $this->player1?->rank }}
                                    <i class="fa-solid fa-trophy"></i>
                                </div>
                            </div>

                            <div class="avatar avatar-peach lg">
                                {{ Str::upper(Str::limit($this->player1?->id === auth()->id() ? $this->player2?->name : $this->player1?->name, 1, '')) }}
                            </div>
                        </div>
                    </div>
                    {{-- TODO Vs  --}}
                    {{-- <div class="dual-bar" id="dual-bar">
                        <div class="dual-bar-blue"></div>
                        <div class="dual-bar-red"></div>
                    </div> --}}
                </div>
                @php
                    $question = $this->currentQuestion;
                @endphp
               
                <div class="question-card">
               
                    <div class="question-head">
                        <span class="question-index">
                            Question {{ $this->current }} / {{ $this->count }}
                        </span>

                        <span class="badge-medium"> {{ $game?->difficulty }} </span>

                    </div>

                    {{-- QUESTION --}}
                    <p class="question-text">{{ $question->content }}</p>

                    {{-- OPTIONS --}}
                    <div class="options">
                        @foreach ($question->options as $option)
                            <div class="option
                                                                                                                                                                                                                                                                                                                                                                                                                            {{ isset($answers[$question->id]) && $answers[$question->id] == $option->id ? 'selected' : '' }}"
                                wire:click="submit({{ $option->id }}, {{ $question->id }})">
                                <span class="option-key">
                                    {{ chr(64 + $loop->iteration) }}
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


                        {{-- TIMER --}}
                        @if ($this->remainingSeconds !== null)
                            <div class="timer" x-data="{
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
                            
                                            $wire.quitGame()
                            
                                        }
                            
                                    }, 1000)
                            
                                }
                            }" x-init="start()">
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
                    <button type="button" class="btn btn-ghost" wire:click="previous" @disabled($this->current === 1)>
                        <i class="fa-solid fa-chevron-left"></i>

                        Previous
                    </button>



                    @if ($this->current !== $this->count)
                        <button type="button" class="btn btn-primary" wire:click="next">
                            Next
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    @else
                        <button type="button" class="btn btn-primary" wire:click="submitAttempt">
                            Submit

                            <i class="fa-solid fa-check"></i>
                        </button>
                    @endif


                    {{-- VALIDATION ERROR --}}
                    @error('answers')
                        <h1 class="text-danger fs-5 my-2 text-center">
                            Please Add Answers Left Questions Answers =
                            {{ $this->count - count($this->answers) }}
                        </h1>
                    @enderror
            </section>

            {{-- ===================== RIGHT SIDEBAR ===================== --}}
            <aside class="side-col">
                {{-- ===================== BATTLE STATUS ===================== --}}
                <div class="panel">
                    <div class="panel-title-row" x-data="{ online: navigator.onLine }" x-init="window.addEventListener('online', () => (online = true));
                    window.addEventListener('offline', () => (online = false));">
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
                      {{-- Reward --}}
                    <div class="stat-row">
                        <i class="fa-solid fa-trophy stat-icon"></i>

                        <div>
                            <div class="stat-label">Win Points</div>

                            <div class="stat-value">{{ $this->currentElo ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="stat-row">
                        <svg class="text-opacity-10 text-danger" xmlns="http://www.w3.org/2000/svg" width="20"
                            height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="4" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-arrow-down">
                            <path d="M12 5v14" />
                            <path d="m19 12-7 7-7-7" />
                        </svg>
                        <div>
                            <div class="stat-label">Lose Points</div>

                            <div class="stat-value">{{ $this->currentInCorrectElo ?? 0 }}</div>
                        </div>
                    </div>

                    {{-- Battle Type --}}
                     <div class="stat-row">
                    <svg class="text-warning" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-refresh-ccw">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                        <path d="M3 3v5h5" />
                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                        <path d="M16 16h5v5" />
                    </svg>
                    <div>
                        <div class="stat-label fw-bold " style="color: var(--text)">Frequency</div>

                        <div class="stat-value">{{ $question->playedCount?->count ?? 0 }} </div>
                    </div>
                </div>
                  
                </div>

                {{-- ===================== BATTLE PROGRESS ===================== --}}
                <div class="panel" wire:show="!finished">
                    <div class="panel-title-row">
                        <span class="panel-title"> Battle Progress </span>

                        <span class="progress-frac"> {{ $current }} / {{ $this->count }} </span>
                    </div>

                    <div class="progress-track">
                        <div class="progress-line-bg"></div>

                        <div class="progress-line-fill"
                            style="
                                                            width:
                                                            {{ $this->count > 1 ? (($current - 1) / ($this->count - 1)) * 90 : 0 }}%;
                                                        ">
                        </div>

                        <div class="progress-dots">
                            @foreach ($game->questions as $question)
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
                <div class="panel justify-content-center align-items-center" wire:show="finished">
                    Waiting For Your Opponent
                </div>

                {{-- ===================== PERFORMANCE ===================== --}}
                <div class="panel center-panel">
                    <div class="panel-title">Opponent Progress</div>

                    <div class="gauge-wrap">
                        <svg viewBox="0 0 140 80" width="150" height="88">
                            <path d="M 13 74 A 54 54 0 0 1 127 74" fill="none" class="gauge-bg" stroke-width="3"
                                stroke-linecap="round" />

                            <path id="gauge-arc" d="M 13 74 A 54 54 0 0 1 127 74" fill="none" class="gauge-arc"
                                stroke-width="3" stroke-linecap="round"
                                stroke-dasharray="{{ count($answers) != 0 ? $this->progress * (100 / $this->count) : 0 }}"
                                pathLength="100" />
                        </svg>

                        <div class="gauge-value">
                            <span class="text-primary"> {{ $this->progress }} </span>
                            / {{ $this->count }}
                        </div>

                        <div class="gauge-label">Progress</div>
                    </div>
                </div>
        </div>

        {{-- ===================== TOPIC ===================== --}}
        {{-- <div class="panel">
            <div class="panel-title">{{ $this->progress }}</div>

            <div class="topic-row">
                <div class="topic-icon">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>

                <div>
                    <div class="topic-name">{{ $game->topic ?? 'Cardiology' }}</div>
                </div>
            </div>
        </div> --}}
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
                themeToggle.addEventListener('click', function() {
                    const isDark = root.getAttribute('data-theme') === 'dark';

                    root.setAttribute('data-theme', isDark ? 'light' : 'dark');

                    themeToggle.innerHTML = isDark ?
                        '<i class="fa-solid fa-moon"></i>' :
                        '<i class="fa-solid fa-sun"></i>';
                });
            }
            let gameId = @js($this->gameId);
            window.Echo.join(`presence-game.${gameId}`)
                .here((users) => {
                    let length = users.length ;
                    $wire.playersCount = length;
                    console.log('Currently connected:', users);
                })
                .joining((user) => {

                })
                .leaving((user) => {
                    console.log('User left:', user);
                });
        </script>
        </script>
    @endscript
</div>

