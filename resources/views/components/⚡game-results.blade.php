<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component {
    public $game;
    public $questions;
    public $attempts;
    public $selectedQuestion = null;

    public function mount($game, ?Collection $questions, ?Collection $attempts): void
    {
        $this->game = $game;
        $this->questions = $questions ?? collect();
        $this->attempts = $attempts ?? collect();
    }

    public function hydrate(): void
    {
        $this->attempts->load(['user', 'answers.option']);
        $this->questions->load(['correctAnswer']);
    }

    public function showQuestion(int $questionId): void
    {
        $this->selectedQuestion = $this->questions->find($questionId);
        $this->selectedQuestion?->loadMissing('options');
    }
};
?>

@php
    $winnerAttempt = $attempts->first();
    $loserAttempt = $attempts->skip(1)->first();
    $winner = $winnerAttempt?->user;
    $loser = $loserAttempt?->user;
    $currentAttempt = $attempts->firstWhere('user_id', auth()->id()) ?? $winnerAttempt;
    $currentPlayer = $currentAttempt?->user;
    $otherAttempt = $currentAttempt?->id === $winnerAttempt?->id ? $loserAttempt : $winnerAttempt;
    $questionCount = $questions->count();
    $winnerScore =
        (int) ($winnerAttempt?->score ?? ($winnerAttempt?->answers->where('is_correct', true)->count() ?? 0));
    $loserScore = (int) ($loserAttempt?->score ?? ($loserAttempt?->answers->where('is_correct', true)->count() ?? 0));
    $scoreMargin = $winnerScore - $loserScore;
    $tempoGap = (int) ($loserAttempt?->time_taken ?? 0) - (int) ($winnerAttempt?->time_taken ?? 0);
    $latestAttempt = $attempts->last();
    $time_taken_attempt = $attempts->sortByDesc("time_taken")->first();
    $totalTime = (int) $time_taken_attempt->time_taken; 
    $formatTime = static fn(?int $seconds): string => sprintf(
        '%02d:%02d',
        intdiv((int) $seconds, 60),
        (int) $seconds % 60,
    );
    $formatSigned = static fn(int $value): string => ($value >= 0 ? '+' : '') . $value;
    $rankChange = (int) ($winnerAttempt?->new_rank ?? 0) - (int) ($winnerAttempt?->current_rank ?? 0);
    $ratingAdjustmentClass = $rankChange < 0 ? 'text-danger' : 'text-success';
@endphp

<main class="battle-main">
    <div class="battle-shell">
        <section class="battle-heading">
            <div>
                <div class="eyebrow">SESSION COMPLETE &middot; MATCH #{{ $game->id }} &middot; 1V1 COMPETITIVE DUEL
                </div>
                <h1>BATTLE COMPLETE <span
                        class="badge text-bg-{{ $winner->id === auth()->id() ? 'success' : 'danger' }}">{{ $winner->id === auth()->id() ? 'Vicotory' : 'Losing' }}</span>
                </h1>
                <p>{{ $winner?->name ?? 'Winner' }} defeated {{ $loser?->name ?? 'the opponent' }}.</p>
            </div>
        </section>

        <section class="deciding-banner">
            <span class="material-symbols-outlined">verified</span>
            <div><strong>DECIDING FACTOR</strong>
                <p>{{ $winner?->name ?? 'The winner' }} won by clinical accuracy <strong
                        class="text-success">({{ $winnerScore }} correct vs {{ $loserScore }} correct &middot;
                        {{ $formatSigned($scoreMargin) }} margin)</strong>
                    @if ($tempoGap !== 0)
                        with {{ abs($tempoGap) }}s {{ $tempoGap > 0 ? 'faster' : 'slower' }} decision tempo
                    @endif.
                </p>
            </div>
            <span class="mono">{{ strtoupper($game->difficulty ?? 'RATED') }} &middot;
                {{ strtoupper($game->length ?? 'MATCH') }}</span>
        </section>

        <section class="duel-grid">
            @foreach ([$winnerAttempt, $loserAttempt] as $index => $attempt)
                @continue(!$attempt)
                @php
                    $player = $attempt->user;
                    $correct = $attempt->answers->where('is_correct', true)->count();
                    $incorrect = $attempt->answers->where('is_correct', false)->count();
                    $passed = max(0, $questionCount - $correct - $incorrect);
                    $accuracy = $questionCount ? round(($correct / $questionCount) * 100) : 0;
                    $viewer = (int) auth()->id() === (int) $player?->id;
                    $image = $player?->image ? asset('storage/' . ltrim($player->image, '/')) : null;
                @endphp
                <article class="battle-card {{ $index === 0 ? 'winner' : '' }}">
                    <div class="player-head">
                        <div class="avatar avatar-large">
                            @if ($image)
                            <img src="{{ $image }}" alt="{{ $player?->name }}">@else<span
                                    class="material-symbols-outlined">person</span>
                            @endif
                        </div>
                        <div><span
                                class="eyebrow {{ $index === 0 ? 'text-success' : 'text-danger' }}">{{ $index === 0 ? 'WINNER' : ($viewer ? 'YOU' : 'OPPONENT') }}
                                &middot; GAME PLAYER</span>
                            <h2>{{ $player?->name ?? 'Unknown player' }}</h2>
                            <p>{{ $player?->country ?: 'Country not set' }}</p>
                        </div><span
                            class="player-score {{ $index === 0 ? 'text-success' : 'text-danger' }}">{{ $attempt->score ?? $correct }}<small>/{{ $questionCount }}</small></span>
                    </div>
                    <div class="rating-line"><span>CLINICAL
                            RATING</span><strong>{{ number_format($attempt->current_rank) }} <em
                                class="{{ $index === 0 ? '' : 'text-danger' }}">{{ $formatSigned((int) $attempt->new_rank - (int) $attempt->current_rank) }}</em></strong>
                    </div>
                    <div class="stat-grid">
                        <div><span>CORRECT</span><strong>{{ $correct }}</strong></div>
                        <div><span>INCORRECT</span><strong>{{ $incorrect }}</strong></div>
                        <div><span>PASSED</span><strong>{{ $passed }}</strong></div>
                        <div><span>TIME TAKEN</span><strong>{{ $formatTime($attempt->time_taken) }}</strong></div>
                    </div>
                    <div class="accuracy">
                        <div><span>DIAGNOSTIC ACCURACY</span><strong>{{ $accuracy }}%</strong></div>
                        <div class="progress">
                            <div class="progress-bar {{ $index === 0 ? 'bg-success' : 'bg-danger' }}"
                                style="width: {{ $accuracy }}%"></div>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="comparison-strip">
            <div><span
                    class="material-symbols-outlined">quiz</span><strong>{{ $questionCount }}</strong><small>QUESTIONS
                    COMPLETED</small></div>
            <div><span
                    class="material-symbols-outlined">difference</span><strong>{{ $formatSigned($scoreMargin) }}</strong><small>SCORE
                    MARGIN</small></div>
            <div><span
                    class="material-symbols-outlined">timer</span><strong>{{ $formatSigned($tempoGap) }}s</strong><small>TEMPO
                    GAP</small></div><button id="cleanMatrix" class="btn">View clean decision matrix <i
                    class="bi bi-arrow-right"></i></button>
        </section>

        <section class="telemetry-grid">
            <article class="data-card"><span
                    class="material-symbols-outlined {{ $ratingAdjustmentClass }}">trending_up</span>
                <div><span>RATING ADJUSTMENT</span><strong
                        class="{{ $ratingAdjustmentClass }}">{{ $formatSigned($rankChange) }}
                        ELO</strong><small>New rating: {{ number_format($winnerAttempt?->new_rank ?? 0) }}</small>
                </div>
            </article>
            <article class="data-card"><span class="material-symbols-outlined">public</span>
                <div><span>OPPONENT
                        RATING</span><strong>{{ number_format($loserAttempt?->new_rank ?? 0) }}</strong><small>{{ $loser?->name ?? 'Opponent' }}</small>
                </div>
            </article>
            <article class="data-card"><span class="material-symbols-outlined text-warning">timer</span>
                <div><span>TOTAL MATCH
                        TIME</span><strong>{{ $formatTime($totalTime) }}</strong><small>{{ $game->ended_at?->format('M j, Y H:i') ?? 'Completed' }}</small>
                </div>
            </article>
        </section>

        <section class="section-card" id="decisionMatrix">
            <div class="section-heading">
                <div>
                    <div class="eyebrow">MATCH TELEMETRY</div>
                    <h2>Questions Matrix</h2>
                </div><span class="mono text-secondary">{{ $questionCount }} QUESTIONS &middot;
                    {{ $formatTime($totalTime) }} TOTAL</span>
            </div>
            <div class="matrix-scroll">
                <div class="matrix-grid ">
                    <div class="matrix-label">QUESTION</div>
                    @foreach ($questions as $question)
                        <button type="button" class=" btn "
                            wire:click="showQuestion({{ $question->id }})">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</button>
                    @endforeach
                    @foreach ([$winnerAttempt, $loserAttempt] as $index => $attempt)
                        @continue(!$attempt)
                        <div class="matrix-label {{ $index === 0 ? 'text-success' : 'text-danger' }}">
                            {{ $attempt->user?->name }} &middot;
                            {{ $attempt->score ?? $attempt->answers->where('is_correct', true)->count() }}</div>
                        @foreach ($questions as $question)
                            @php $answer = $attempt->answers->firstWhere('question_id', $question->id); @endphp<div
                                class="matrix-cell {{ $answer?->is_correct ? 'correct' : ($answer ? 'miss' : 'unanswered') }}"
                                wire:click="showQuestion({{ $question->id }})" role="button" tabindex="0">
                                {!! $answer?->is_correct ? '&#10003;' : ($answer ? '&times;' : '&mdash;') !!}</div>
                        @endforeach
                    @endforeach
                </div>
            </div>
            <div class="matrix-legend"><span><i class="correct"></i> Concordant correct</span><span><i
                        class="miss"></i> Missed decision</span><span><i class="unanswered"></i> Unanswered /
                    passed</span></div>
        </section>

        <section class="section-card review-section" id="review-section">
            <div class="section-heading">
                <div>
                    <div class="eyebrow">POST-MATCH ANALYSIS</div>
                    <h2>Review the battle</h2>
                    <p>Every question is calculated from the recorded answers.</p>
                </div>
                <div class="btn-group" id="reviewFilters" role="group"><button class="btn btn-primary active"
                        data-filter="all">All moments</button><button class="btn btn-outline-secondary"
                        data-filter="missed">Missed</button><button class="btn btn-outline-secondary"
                        data-filter="correct">Correct</button></div>
            </div>
            <div class="review-list">
                @foreach ($questions as $question)
                    @php
                        $currentAnswer = $currentAttempt?->answers->firstWhere('question_id', $question->id);
                        $otherAnswer = $otherAttempt?->answers->firstWhere('question_id', $question->id);
                        $status = $currentAnswer?->is_correct ? 'correct' : 'missed';
                        $statusLabel = ['missed' => 'MISSED', 'correct' => 'CORRECT'][$status];
                        $correctOption = $question->correctAnswer;
                        $questionTitle = $question->name ?: Str::limit(strip_tags($question->content), 100);
                        $answerName = static fn($answer) => $answer
                            ? ($answer->option?->name ?:
                            $answer->option?->content ?:
                            'Unknown option')
                            : 'No answer';
                        $statusColor = $status === 'correct' ? 'success' : 'danger';
                    @endphp
                    <article class="review-card" data-status="{{ $status }}">
                        <div class="review-index text-{{ $statusColor }}">
                            Q{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                        <div>
                            <div class="review-card-title"><strong>{{ ucfirst($status) }} decision</strong><span
                                    class="badge text-bg-{{ $statusColor }}">{{ $statusLabel }}</span></div>
                            <h3>{{ $questionTitle }}</h3>
                            <p>{{ $question->main_explanation ?: 'No explanation was recorded for this question.' }}
                            </p>
                            <div class="review-meta"><span
                                    class="{{ $currentAnswer?->is_correct ? 'text-success' : 'text-danger' }}">Your
                                    answer ({{ $currentPlayer?->name }}):
                                    {{ Str::limit($answerName($currentAnswer), 60) }}</span><span
                                    class="{{ $otherAnswer?->is_correct ? 'text-success' : 'text-danger' }}">{{ $otherAttempt?->user?->name }}:
                                    {{ Str::limit($answerName($otherAnswer), 60) }}</span><span
                                    class="text-success">Correct:
                                    {{ Str::limit($correctOption?->name ?: $correctOption?->content ?: 'Not available', 60) }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        @if ($selectedQuestion)
            @php
                $modalWinnerAnswer = $winnerAttempt?->answers->firstWhere('question_id', $selectedQuestion->id);
                $modalLoserAnswer = $loserAttempt?->answers->firstWhere('question_id', $selectedQuestion->id);
                $modalWinnerOption = $selectedQuestion->options->firstWhere('id', $modalWinnerAnswer?->option_id);
                $modalLoserOption = $selectedQuestion->options->firstWhere('id', $modalLoserAnswer?->option_id);
            @endphp
            <div class="results-question-backdrop" wire:click="showQuestion(0)" role="presentation">
                <section class="results-question-modal question-panel" wire:click.stop role="dialog" aria-modal="true"
                    aria-labelledby="resultsQuestionTitle">
                    <div class="panel-scroll">
                        <div class="panel-topline"><span>QUESTION
                                {{ str_pad((int) $questions->search(fn($question) => $question->id === $selectedQuestion->id) + 1, 2, '0', STR_PAD_LEFT) }}
                                &middot; {{ $selectedQuestion->topic }}</span><button type="button"
                                class="results-modal-close" wire:click="showQuestion(0)" aria-label="Close question"><i
                                    class="bi bi-x-lg"></i></button></div>
                        <h2 id="resultsQuestionTitle">{{ $selectedQuestion->name ?: 'Clinical question' }}</h2>
                        <div class="case-vignette">{!! $selectedQuestion->content !!}</div>
                        <div class="answer-list" role="list" aria-label="Answer options">
                            @foreach ($selectedQuestion->options as $option)
                                @php
                                    $isCorrectOption =
                                        (int) $selectedQuestion->correctAnswer?->id === (int) $option->id;
                                    $winnerSelected = (int) $modalWinnerOption?->id === (int) $option->id;
                                    $loserSelected = (int) $modalLoserOption?->id === (int) $option->id;
                                @endphp
                                <div
                                    class="answer-item answer-option results-answer-option {{ $isCorrectOption ? 'correct-answer' : '' }} {{ $winnerSelected ? 'winner-selected' : '' }} {{ $loserSelected ? 'loser-selected' : '' }}">
                                    <div class="answer-choice"><span
                                            class="letter results-answer-letter">{{ $option->name ?: chr(64 + $loop->iteration) }}</span><span
                                            class="results-answer-text">{{ $option->content }}</span><span
                                            class="results-answer-tags">
                                            @if ($isCorrectOption)
                                                <small class="answer-tag correct-tag">Correct answer</small>
                                            @endif
                                            @if ($winnerSelected)
                                                <small class="answer-tag winner-tag">{{ $winner?->name }} chose
                                                    this</small>
                                            @endif
                                            @if ($loserSelected)
                                                <small class="answer-tag loser-tag">{{ $loser?->name }} chose
                                                    this</small>
                                            @endif
                                        </span><span class="radio"></span></div>
                                    @if ($option->explanation)
                                        <div class="option-explanation">{!! $option->explanation !!}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="results-player-choices"><span><strong>{{ $winner?->name }}:</strong>
                                {{ $modalWinnerOption?->name ?: $modalWinnerOption?->content ?: 'No answer' }}</span><span><strong>{{ $loser?->name }}:</strong>
                                {{ $modalLoserOption?->name ?: $modalLoserOption?->content ?: 'No answer' }}</span>
                        </div>
                        <div class="explanation-card results-explanation"><strong><i class="bi bi-lightbulb-fill"></i>
                                HIGH YIELD</strong>
                            <div>{!! $selectedQuestion->high_yield ?: 'No high-yield note was recorded.' !!}</div><strong><i class="bi bi-check-circle-fill"></i> MAIN
                                EXPLANATION</strong>
                            <div>{!! $selectedQuestion->main_explanation ?: 'No explanation was recorded for this question.' !!}</div>
                        </div>
                    </div>
                </section>
            </div>
        @endif

        <section class="bottom-actions"><a class="btn btn-outline-secondary" href="{{ route('start.game') }}"><i
                    class="bi bi-arrow-left"></i> Back to Arena Lobby</a><button class="btn btn-outline-secondary"
                id="reviewMistakes"><i class="bi bi-search"></i> Review mistakes</button><button id="fullTelemetry"
                class="btn btn-outline-secondary" type="button"><i class="bi bi-activity"></i> Full
                Telemetry</button><a class="btn btn-primary" href="{{ route('start.game') }}"><i
                    class="bi bi-lightning-charge"></i> Queue Next Match</a></section>
    </div>
</main>
