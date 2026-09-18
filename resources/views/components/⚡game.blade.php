<?php

use App\Events\GameFinished;
use App\Events\GameNotifications;
use App\Events\playerAnswered;
use App\Models\Game;
use App\Models\GameAnswers;
use App\Models\Option;
use App\Models\Questions;
use App\Models\QuestionPlayedTime;
use App\Services\GameService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $game;
    public $attempt;
    public $currentPlayer;
    public $currentQuestion;
    public int $gameId;
    public int $userId;
    public int $current = 1;
    public int $progress = 0;
    public array $answers = [];
    public bool $loading = true;
    public bool $finished = false;
    public bool $opponentOnline = false;
    public string $message = '';
    public ?bool $winner = null;
    public string $opponentName = 'Opponent';
    public string $opponentInitials = '?';

    public function mount(int $gameId): void
    {
        $this->gameId = $gameId;
        $this->userId = (int) auth()->id();
        $this->refreshState();
        abort_unless($this->currentPlayer, 403, 'You are not a player in this game.');
    }

    private function refreshState(): void
    {
        $this->game = Game::query()
            ->with(['questions', 'players.user', 'attempts'])
            ->findOrFail($this->gameId);
        $this->currentPlayer = $this->game->players->firstWhere('user_id', auth()->id());
        $this->attempt = $this->game->attempts->firstWhere('user_id', auth()->id());
        $opponent = $this->game->players->first(fn($player) => (int) $player->user_id !== (int) auth()->id())?->user;
        $this->opponentName = $opponent?->name ?? 'Opponent';
        $this->opponentInitials = strtoupper(substr($this->opponentName, 0, 2));
        $this->opponentOnline = $opponent !== null && ! $this->game->disconnected_at;

        if (!$this->currentPlayer || !$this->attempt) {
            return;
        }

        $this->current = max(1, min((int) $this->currentPlayer->current_question, max(1, $this->game->questions->count())));
        $this->finished = $this->attempt->status === 'finished';
        $this->loading = $this->game->status !== 'playing';
        $this->answers = GameAnswers::query()
            ->where('game_attempt_id', $this->attempt->id)
            ->where('player_id', auth()->id())
            ->pluck('option_id', 'question_id')
            ->mapWithKeys(fn($optionId, $questionId) => [(string) $questionId => (int) $optionId])
            ->all();
        $this->getProgress();
        $this->loadQuestion();
    }

    #[Computed]
    public function count(): int
    {
        return $this->game?->questions->count() ?? 0;
    }

    #[Computed]
    public function answeredCount(): int
    {
        return count($this->answers);
    }

    #[Computed]
    public function accuracy(): int
    {
        if (!$this->attempt || $this->answeredCount === 0) {
            return 0;
        }
        $correct = GameAnswers::query()->where('game_attempt_id', $this->attempt->id)->where('is_correct', true)->count();

        return (int) round(($correct / $this->answeredCount) * 100);
    }

    #[Computed]
    public function remainingSeconds(): ?int
    {
        if (!$this->game?->ended_at) {
            return null;
        }

        return max(0, now()->diffInSeconds($this->game->ended_at, false));
    }

    #[Computed]
    public function disconnectRemainingSeconds(): ?int
    {
        if (! $this->game?->disconnected_at) {
            return null;
        }

        $disconnectedAt = $this->game->disconnected_at instanceof \Carbon\Carbon
            ? $this->game->disconnected_at
            : \Carbon\Carbon::parse($this->game->disconnected_at);

        return max(0, 120 - (now()->timestamp - $disconnectedAt->timestamp));
    }

    public function loadQuestion(): void
    {
        $questionId = $this->game?->questions->get($this->current - 1)?->id;
        if (!$questionId) {
            $this->currentQuestion = null;

            return;
        }

        $cachedQuestion = Cache::remember("game-question-v2:{$questionId}", now()->addHour(), function () use ($questionId): array {
            $question = Questions::with('options', 'correctAnswer', 'playedCount')->findOrFail($questionId);

            return [
                'question' => $question->withoutRelations()->toArray(),
                'options' => $question->options->toArray(),
                'correctAnswer' => $question->correctAnswer?->toArray(),
                'playedCount' => $question->playedCount?->toArray(),
            ];
        });

        $this->currentQuestion = Questions::hydrate([$cachedQuestion['question']])->firstOrFail();
        $this->currentQuestion->setRelation('options', Option::hydrate($cachedQuestion['options']));
        $this->currentQuestion->setRelation('correctAnswer', $cachedQuestion['correctAnswer'] ? Option::hydrate([$cachedQuestion['correctAnswer']])->first() : null);
        $this->currentQuestion->setRelation('playedCount', $cachedQuestion['playedCount'] ? QuestionPlayedTime::hydrate([$cachedQuestion['playedCount']])->first() : null);
    }

    public function submit(int $optionId, int $questionId): void
    {
        $this->refreshState();
        if (!$this->attempt || !$this->isPlayable()) {
            return;
        }

        $question = $this->game
            ->questions()
            ->with(['correctAnswer', 'options'])
            ->findOrFail($questionId);
        if (!$question->options->contains('id', $optionId) || !$question->correctAnswer) {
            return;
        }

        $this->attempt->answers()->updateOrCreate(['question_id' => $questionId, 'player_id' => auth()->id()], ['option_id' => $optionId, 'is_correct' => $question->correctAnswer->id === $optionId]);
        $this->answers[(string) $questionId] = $optionId;

        if (config('cache.default') === 'redis') {
            $key = "game:{$this->gameId}:attempt:{$this->attempt->id}:answers";
            Redis::connection('cache')->sadd($key, (string) $questionId);
            Redis::connection('cache')->expire($key, 86400);
        }

        $milestone = $this->answeredCount;
        $milestoneKey = "game:{$this->gameId}:notifications:{$milestone}";
        if (in_array($milestone, [5, 10, 15], true) && Cache::add($milestoneKey, true, now()->addDay())) { 
            $attempts = $this->game
                ->attempts()
                ->with(['answers', 'user'])
                ->get();
            if ($attempts->count() === 2) {
                foreach (app(GameService::class)->getMessage($attempts) as $notification) {
                    GameNotifications::dispatch($notification['user'], $notification['message'], $notification['winning'], $notification['draw']);
                }
            }
        }

        playerAnswered::dispatch(auth()->id(), $this->gameId);
    }

    public function updateCurrent(int $current): void
    {
        if (!$this->game || $current < 1 || $current > $this->count) {
            return;
        }

        $this->current = $current;
        $this->currentPlayer->update(['current_question' => $current]);
        $this->loadQuestion();
    }

    public function next(): void
    {
        $this->updateCurrent($this->current + 1);
    }

    public function previous(): void
    {
        $this->updateCurrent($this->current - 1);
    }

    public function getProgress(): void
    {
        if (!$this->game) {
            return;
        }

        $opponentAttempt = $this->game->attempts->firstWhere('user_id', '!=', auth()->id());
        if (!$opponentAttempt) {
            $this->progress = 0;

            return;
        }

        if (config('cache.default') === 'redis') {
            $progress = (int) Redis::connection('cache')->scard("game:{$this->gameId}:attempt:{$opponentAttempt->id}:answers");
            if ($progress > 0) {
                $this->progress = $progress;

                return;
            }
        }

        $this->progress = GameAnswers::query()->where('game_attempt_id', $opponentAttempt->id)->where('player_id', $opponentAttempt->user_id)->count();
    }

    #[On('echo-private:game.{gameId},.game.started')]
    public function gameStarted(): void
    {
        $this->refreshState();
        $this->dispatch('game-timer-started', seconds: $this->remainingSeconds);
    }

    #[On('echo-private:playerAnswerd.{gameId},.game.progress')]
    public function opponentProgressed(): void
    {
        $this->refreshState();
    }

    #[On('echo-private:user.{userId},.user.message')]
    public function getMessage(array $event): void
    {
        $this->winner = $event['winner'] ?? null;
        $this->message = $event['message'] ?? '';
        $this->dispatch('game-notification', message: $this->message, winner: $this->winner, draw: $event['draw'] ?? false);
    }

    /** Presence callbacks are sent by the Alpine Echo subscription below. */
    public function syncPresence(array $userIds = []): void
    {
        $opponent = $this->game?->players->first(fn($player) => (int) $player->user_id !== (int) auth()->id());
        if (!$opponent) {
            return;
        }

        in_array((int) $opponent->user_id, array_map('intval', $userIds), true) ? $this->playerConnected($opponent->user_id) : $this->playerDisconnected($opponent->user_id);
    }

    public function playerDisconnected(?int $userId = null): void
    {
        if (!$this->isOpponent($userId) || !$this->isPlaying()) {
            return;
        }

        $this->opponentOnline = false;
        if (!$this->game->disconnected_at) {
            $this->game->update(['disconnected_at' => now()]);
        }
        $this->dispatch('disconnect-timer-started', seconds: $this->disconnectRemainingSeconds);
    }

    public function playerConnected(?int $userId = null): void
    {
        if (!$this->isOpponent($userId)) {
            return;
        }

        $this->opponentOnline = true;
        if ($this->game->disconnected_at) {
            $this->game->update(['disconnected_at' => null]);
        }
        $this->dispatch('disconnect-timer-stopped');
    }

    public function submitAttempt()
    {
        $this->refreshState();
        if (!$this->attempt || $this->answeredCount !== $this->count) {
            $this->addError('answers', 'Please answer all questions before finishing.');

            return null;
        }

        $this->finishCurrentAttempt();

        return $this->redirectIfGameFinished();
    }

    /** Called by Alpine when the authoritative server end time reaches zero. */
    public function expireGame()
    {
        $this->refreshState();
        if (!$this->game->ended_at || now()->lt($this->game->ended_at)) {
            return null;
        }

        if ($this->game->status !== 'finished' && app(GameService::class)->completeGame($this->game)) {
            GameFinished::dispatch($this->gameId);
        }

        return $this->redirectIfGameFinished();
    }

    /** Called when the opponent's two-minute reconnect window expires. */
    public function expireDisconnectedOpponent()
    {
        $this->refreshState();
        if (! $this->game->disconnected_at || $this->disconnectRemainingSeconds > 0) {
            return null;
        }

        if ($this->game->status !== 'finished' && app(GameService::class)->completeGame($this->game)) {
            GameFinished::dispatch($this->gameId);
        }

        return $this->redirectIfGameFinished();
    }

    #[On('echo-private:game.finished.{gameId},.game.finished')]
    public function toResults()
    {
        return $this->redirectIfGameFinished();
    }

    #[On('quit-quiz')]
    public function quitGame()
    {
        $this->refreshState();
        if ($this->game->status !== 'finished' && app(GameService::class)->completeGame($this->game)) {
            GameFinished::dispatch($this->gameId);
        }

        return $this->redirectIfGameFinished();
    }

    private function finishCurrentAttempt(): void
    {
        if (!$this->isPlayable()) {
            return;
        }

        $this->currentPlayer->update(['status' => 'finished']);
        app(GameService::class)->editAttempt($this->attempt, $this->game);
        $this->finished = true;
        $this->refreshState();

        if ($this->game->players->every(fn($player) => $player->status === 'finished') && app(GameService::class)->finishGame($this->game->attempts)) {
            GameFinished::dispatch($this->gameId);
        }
    }

    private function redirectIfGameFinished()
    {
        $this->game->refresh();
        if ($this->game->status !== 'finished') {
            return null;
        }

        return $this->redirectRoute('game.results', ['game' => $this->gameId], navigate: false);
    }

    private function isPlayable(): bool
    {
        return $this->isPlaying() && !$this->finished && !($this->game->ended_at && now()->gte($this->game->ended_at));
    }

    private function isPlaying(): bool
    {
        return $this->game && $this->game->status === 'playing';
    }

    private function isOpponent(?int $userId): bool
    {
        return $this->game && (int) $userId !== (int) auth()->id() && $this->game->players->contains('user_id', $userId);
    }
};
?>

<div class="game-screen" x-data="{
    navOpen: false,
    remaining: {{ $this->remainingSeconds ?? 0 }},
    timer: null,
    disconnectRemaining: {{ $this->disconnectRemainingSeconds === null ? 'null' : $this->disconnectRemainingSeconds }},
    disconnectTimer: null,
    hasTimer: {{ $game->ended_at ? 'true' : 'false' }},
     imageModal: false,
     notificationTimer: null,
     winningLoaderTimer: null,
     losingLoaderTimer: null,
     drawLoaderTimer: null,
    init() {
        this.startTimer();
        this.startDisconnectTimer();
        this.$nextTick(() => this.connectPresence());
    },
    startTimer() {
        if (!this.hasTimer || this.timer) return;
        if (this.remaining <= 0) { this.$wire.expireGame(); return; }
        this.timer = setInterval(() => {
            this.remaining = Math.max(0, this.remaining - 1);
            if (this.remaining === 0) {
                clearInterval(this.timer);
                this.timer = null;
                this.$wire.expireGame();
            }
        }, 1000);
    },
    restartTimer(seconds) {
        if (this.timer) clearInterval(this.timer);
        this.timer = null;
        this.hasTimer = seconds !== null;
        this.remaining = seconds ?? 0;
        this.startTimer();
    },
    startDisconnectTimer() {
        if (this.disconnectTimer) clearInterval(this.disconnectTimer);
        this.disconnectTimer = null;
        if (this.disconnectRemaining === null) return;
        if (this.disconnectRemaining <= 0) { this.$wire.expireDisconnectedOpponent(); return; }
        this.disconnectTimer = setInterval(() => {
            this.disconnectRemaining = Math.max(0, this.disconnectRemaining - 1);
            if (this.disconnectRemaining === 0) {
                clearInterval(this.disconnectTimer);
                this.disconnectTimer = null;
                this.$wire.expireDisconnectedOpponent();
            }
        }, 1000);
    },
    restartDisconnectTimer(seconds) {
        this.disconnectRemaining = seconds;
        this.startDisconnectTimer();
    },
    connectPresence() {
        if (!window.Echo) {
            window.addEventListener('echo:ready', () => this.connectPresence(), { once: true });
            return;
        }
        const channelName = 'presence-game.{{ $gameId }}';
        window.medGambitPresenceChannels ??= {};
        if (window.medGambitPresenceChannels[channelName]) return;
        window.medGambitPresenceChannels[channelName] = true;
        window.Echo.join(channelName)
            .here(users => this.$wire.syncPresence(users.map(user => Number(user?.id)).filter(id => Number.isInteger(id) && id > 0)))
            .joining(user => user?.id && this.$wire.playerConnected(Number(user.id)))
            .leaving(user => user?.id && this.$wire.playerDisconnected(Number(user.id)));
    },
    destroy() {
        if (this.timer) clearInterval(this.timer);
        if (this.disconnectTimer) clearInterval(this.disconnectTimer);
    },
     showNotification(detail) {
         if (!detail.message) return;
         const toast = document.getElementById('game-notification-toast');
         if (!toast) return;

        toast.querySelector('[data-game-notification-icon]').className = detail.winner ? 'bi bi-trophy-fill' : 'bi bi-activity';
        toast.querySelector('[data-game-notification-message]').textContent = detail.message;
         toast.classList.add('is-visible');
         clearTimeout(this.notificationTimer);
         this.notificationTimer = setTimeout(() => toast.classList.remove('is-visible'), 4500);

         if (detail.winner === true) {
             window.EcgLoader?.show();
             clearTimeout(this.winningLoaderTimer);
             this.winningLoaderTimer = setTimeout(() => window.EcgLoader?.hide(), 4500);
             window.FlatlineLoader?.hide();
         } else if (detail.draw === true) {
             window.EcgLoader?.hide();
             window.FlatlineLoader?.hide();
             window.DrawLoader?.show();
             clearTimeout(this.drawLoaderTimer);
             this.drawLoaderTimer = setTimeout(() => window.DrawLoader?.hide(), 4500);
         } else if (detail.winner === false) {
             window.EcgLoader?.hide();
             window.FlatlineLoader?.show();
             clearTimeout(this.losingLoaderTimer);
             this.losingLoaderTimer = setTimeout(() => window.FlatlineLoader?.hide(), 4500);
         } else {
             window.EcgLoader?.hide();
             window.FlatlineLoader?.hide();
         }
     },
    formatTime() { return `${String(Math.floor(this.remaining / 60)).padStart(2, '0')}:${String(this.remaining % 60).padStart(2, '0')}`; },
    formatDisconnectTime() { return `${String(Math.floor(this.disconnectRemaining / 60)).padStart(2, '0')}:${String(this.disconnectRemaining % 60).padStart(2, '0')}`; }
}" x-on:game-timer-started.window="restartTimer($event.detail.seconds)"
    x-on:game-notification.window="showNotification($event.detail)"
    x-on:disconnect-timer-started.window="restartDisconnectTimer($event.detail.seconds)"
    x-on:disconnect-timer-stopped.window="restartDisconnectTimer(null)">
    @if ($loading)
        <div class="game-state" role="status"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            Waiting for the other player to start the duel…</div>
    @elseif ($currentQuestion)
        <main class="quiz-main" aria-live="polite">
            <div class="arena-strip">
                <div><span class="live-pill"><i></i> LIVE 1v1 DUEL</span><span
                        class="arena-label">{{ ucfirst($game->difficulty ?? 'ranked') }} ·
                        {{ ucfirst($game->length ?? 'match') }} mode</span></div>
                <div class="scoreboard">
                    <span><b>{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</b> You
                        <strong>{{ $this->answeredCount }}/{{ $this->count }}</strong></span><em>VS</em>
                    <span><b class="opponent">{{ $opponentInitials }}</b>{{ $opponentName }}
                        <strong>{{ $progress }}/{{ $this->count }}</strong></span>
                </div>
            </div>

            <button class="mobile-nav-trigger" type="button" x-on:click="navOpen = !navOpen"
                x-bind:aria-expanded="navOpen.toString()"><i class="bi bi-list"></i> Question {{ $current }} of
                {{ $this->count }} · {{ $currentQuestion->name }}</button>
            <div class="quiz-layout">
                <aside class="question-sidebar" x-bind:class="{ 'open': navOpen }" aria-label="Question navigator">
                    <div class="sidebar-head">
                        <div class="sidebar-title"><span class="live-dot"></span> QUIZ NAVIGATOR <small>LIVE</small>
                        </div>
                        <div class="progress-row"><strong>Question <span>{{ $current }}</span> <small>of
                                    {{ $this->count }}</small></strong><span>{{ $this->count ? round(($this->answeredCount / $this->count) * 100) : 0 }}%
                                complete</span></div>
                        <div class="progress-track"><i
                                style="width: {{ $this->count ? ($this->answeredCount / $this->count) * 100 : 0 }}%"></i>
                        </div>
                        <div class="sidebar-meta"><span class="{{ $opponentOnline ? '' : 'opponent-offline' }}">●
                                {{ $opponentName }}: {{ $progress }}/{{ $this->count }}
                                {{ $opponentOnline ? '' : '(offline)' }}</span>
                            @if ($message)
                                <b>{{ $message }}</b>
                            @endif
                        </div>
                        @if ($this->disconnectRemainingSeconds !== null)
                            <div class="disconnect-countdown" x-show="disconnectRemaining !== null">
                                <i class="bi bi-wifi-off"></i> Reconnect window
                                <strong x-text="formatDisconnectTime()"></strong>
                            </div>
                        @endif
                    </div>
                    <div class="question-list" role="list">
                        @foreach ($game->questions as $index => $question)
                            @php($number = $index + 1)
                            <button wire:key="game-question-{{ $question->id }}"
                                class="question-item {{ $current === $number ? 'current' : '' }} {{ isset($answers[(string) $question->id]) ? 'answered' : '' }}"
                                type="button" wire:click="updateCurrent({{ $number }})"
                                x-on:click="navOpen = false" role="listitem">
                                <span class="q-label"><span
                                        class="q-num">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</span><span
                                        class="q-name">{{ $question->name }}</span></span><span
                                    class="q-status">{{ isset($answers[(string) $question->id]) ? '✓' : ($current === $number ? '●' : '') }}</span>
                            </button>
                        @endforeach
                    </div>
                    <div class="sidebar-foot"><span>Choose any question to
                            jump</span><strong>{{ $this->answeredCount }}/{{ $this->count }} done</strong></div>
                </aside>

                <section class="case-card" aria-labelledby="caseTitle">
                    <div class="case-header">
                        <div class="case-meta"><span>QUESTION <b>{{ $current }}</b> OF
                                {{ $this->count }}</span><span>{{ $currentQuestion->topic ?: 'Clinical medicine' }}</span><span
                                class="{{ $opponentOnline ? '' : 'opponent-offline' }}">● Opponent:
                                {{ $opponentName }}{{ $opponentOnline ? '' : ' (offline)' }}</span></div>
                        <div class="case-tools"><span class="accuracy">Accuracy: <b>{{ $this->accuracy }}%</b></span>
                            @if ($game->ended_at)
                                <span class="timer" x-bind:class="{ 'timer-expired': remaining === 0 }"><i
                                        class="bi bi-clock"></i> <b x-text="formatTime()"></b></span>
                            @endif
                        </div>
                    </div>
                    <div class="question-scroll">
                        <div class="question-heading">
                            <div><span class="eyebrow">CASE {{ $current }} ·
                                    {{ $currentQuestion->topic ?: 'CLINICAL MEDICINE' }}</span>
                                <h1 id="caseTitle">{{ $currentQuestion->name }}</h1>
                            </div><span class="type-badge">SINGLE CHOICE</span>
                        </div>
                        <div class="vignette">{!! $currentQuestion->content !!}</div>
                        @if ($currentQuestion->image)
                            <button class="question-image-trigger" type="button" x-on:click="imageModal = true">
                                <i class="bi bi-image"></i>
                                <span><strong>See image</strong><small>Open the supporting clinical image</small></span>
                                <i class="bi bi-arrow-up-right ms-auto"></i>
                            </button>
                        @endif
                        <div class="prompt"><span class="eyebrow">SELECT THE MOST APPROPRIATE ANSWER</span></div>
                        <div class="quiz-options" role="radiogroup" aria-label="Answer options">
                            @foreach ($currentQuestion->options as $option)
                                <button wire:key="game-option-{{ $option->id }}"
                                    class="quiz-option {{ ($answers[(string) $currentQuestion->id] ?? null) === $option->id ? 'is-selected' : '' }}"
                                    type="button"
                                    wire:click="submit({{ $option->id }}, {{ $currentQuestion->id }})"
                                    wire:loading.attr="disabled" @disabled($finished)
                                    aria-checked="{{ ($answers[(string) $currentQuestion->id] ?? null) === $option->id ? 'true' : 'false' }}">
                                    <span class="letter-badge">{{ $option->name }}</span><span
                                        class="answer-text">{{ $option->content }}</span><span
                                        class="check-indicator"><i></i></span>
                                </button>
                            @endforeach
                        </div>
                        @if ($finished && $currentQuestion->main_explanation)
                            <div class="explanation"><strong><i class="bi bi-info-circle-fill"></i> CLINICAL
                                    EXPLANATION</strong>
                                <p>{{ $currentQuestion->main_explanation }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="quiz-actions">
                        <div class="keyboard-hint">Please Answer All The Questions </div>
                        <div class="action-buttons">
                            <button class="btn btn-outline-secondary" type="button" wire:click="previous"
                                @disabled($current === 1)><i class="bi bi-chevron-left"></i> Previous</button>
                            @if ($current < $this->count)
                                <button class="btn btn-primary" type="button" wire:click="next">Next question <i
                                        class="bi bi-chevron-right"></i></button>
                            @else
                                <button class="btn btn-primary" type="button" wire:click="submitAttempt"
                                    @disabled($finished)>Finish game <i class="bi bi-check-lg"></i></button>
                            @endif
                        </div>
                    </div>
                </section>
            </div>
        </main>
    @else
        <div class="game-state">This game has no questions available.</div>
    @endif
    @if ($currentQuestion?->image)
        <div class="question-image-modal" x-cloak x-show="imageModal" x-transition.opacity
            x-on:keydown.escape.window="imageModal = false" role="dialog" aria-modal="true"
            aria-labelledby="imageModalTitle">
            <button class="question-image-modal-backdrop" type="button" x-on:click="imageModal = false"
                aria-label="Close image preview"></button>
            <section class="question-image-modal-dialog">
                <header class="question-image-modal-header">
                    <div><span class="eyebrow">SUPPORTING CLINICAL IMAGE</span>
                        <h2 id="imageModalTitle">{{ $currentQuestion->name }}</h2>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" type="button" x-on:click="imageModal = false"
                        aria-label="Close image preview"><i class="bi bi-x-lg"></i></button>
                </header>
                <div class="question-image-modal-body">
                    <img src="{{ $currentQuestion->image }}"
                        alt="Supporting image for {{ $currentQuestion->name }}">
                    <aside class="question-image-caption">
                        <span class="eyebrow">IMAGE CAPTION</span>
                        <p>{{ $currentQuestion->name }}</p>
                        @if ($currentQuestion->topic)
                            <small>{{ $currentQuestion->topic }}</small>
                        @endif
                    </aside>
                </div>
            </section>
        </div>
     @endif
    <ecg-loader hidden wire:ignore></ecg-loader>
    <main class="flatline-stage" hidden wire:ignore aria-label="Losing notification">
        <svg class="flatline-svg" viewBox="0 0 1000 300" role="img" aria-labelledby="flatline-title">
            <title id="flatline-title">Animated flatline</title>
            <defs>
                <filter id="line-glow" x="-30%" y="-400%" width="160%" height="900%">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="2" result="blur-tight" />
                    <feGaussianBlur in="SourceGraphic" stdDeviation="5" result="blur-med" />
                    <feGaussianBlur in="SourceGraphic" stdDeviation="12" result="blur-wide" />
                    <feMerge>
                        <feMergeNode in="blur-wide" /><feMergeNode in="blur-med" />
                        <feMergeNode in="blur-tight" /><feMergeNode in="SourceGraphic" />
                    </feMerge>
                </filter>
            </defs>
            <line class="flatline" x1="150" y1="150" x2="850" y2="150" pathLength="700" filter="url(#line-glow)" />
        </svg>
    </main>
    <main class="draw-stage" hidden wire:ignore aria-label="Draw notification">
        <svg class="draw-svg" viewBox="0 0 1000 400" role="img" aria-labelledby="draw-title">
            <title id="draw-title">Animated draw waveform</title>
            <defs>
                <filter id="draw-pulse-glow" x="-30%" y="-30%" width="160%" height="160%">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="2.5" result="blur" />
                    <feMerge><feMergeNode in="blur" /><feMergeNode in="SourceGraphic" /></feMerge>
                </filter>
                <filter id="draw-pen-flare" x="-100%" y="-100%" width="300%" height="300%">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="5" result="b1" />
                    <feGaussianBlur in="SourceGraphic" stdDeviation="2" result="b2" />
                    <feMerge><feMergeNode in="b1" /><feMergeNode in="b2" /><feMergeNode in="SourceGraphic" /></feMerge>
                </filter>
                <radialGradient id="pen-bloom-draw" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="var(--draw-start)" />
                    <stop offset="35%" stop-color="var(--draw-highlight)" stop-opacity=".8" />
                    <stop offset="70%" stop-color="var(--draw-primary)" stop-opacity=".3" />
                    <stop offset="100%" stop-color="var(--draw-primary)" stop-opacity="0" />
                </radialGradient>
                <linearGradient id="draw-stroke-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="var(--draw-start)" /><stop offset="36%" stop-color="var(--draw-start)" />
                    <stop offset="43%" stop-color="var(--draw-highlight)" /><stop offset="48%" stop-color="var(--draw-primary)" />
                    <stop offset="100%" stop-color="var(--draw-primary)" />
                </linearGradient>
            </defs>
            <g class="draw-stage-master">
                <path class="drawn-waveform-path" d="M 100 200 L 460 200 C 476 200, 488 110, 508 110 C 528 110, 538 200, 552 200 C 566 200, 576 290, 596 290 C 616 290, 628 200, 644 200 L 900 200" pathLength="940" fill="none" filter="url(#draw-pulse-glow)" stroke="url(#draw-stroke-gradient)" stroke-width="3" />
                <g class="pen-tracker" pointer-events="none">
                    <circle cx="0" cy="0" r="16" fill="url(#pen-bloom-draw)" />
                    <circle class="pen-bloom-circle" cx="0" cy="0" r="4.5" filter="url(#draw-pen-flare)" />
                    <circle cx="0" cy="0" r="2.2" fill="var(--draw-start)" />
                </g>
            </g>
        </svg>
    </main>
    <div id="game-notification-toast" class="game-notification" wire:ignore role="status" aria-live="polite">
        <i class="bi bi-activity" data-game-notification-icon></i>
        <div><small>Match update</small><strong data-game-notification-message></strong></div>
    </div>
</div>
