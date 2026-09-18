@extends('layouts.main')

@section('title', 'Quiz results')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-results.css') }}">
@endpush

@php
    $topic = $quiz->topic ?: 'Clinical medicine';
    $timeTaken = (int) ($attempt->time_taken ?? 0);
    $minutes = intdiv($timeTaken, 60);
    $seconds = $timeTaken % 60;
    $score = $correctCount;
    $previousRank = $attempt->current_rank;
    $newRank = $attempt->new_rank;
    $rankChange = $newRank !== null && $previousRank !== null ? $newRank - $previousRank : null;
@endphp

@section('content')
    <div class="results-page results-main">
        <div class="results-shell">
            <div class="results-meta"><span><span class="status-dot" aria-hidden="true"></span> Session completed ·
                    {{ $topic }}</span><span class="results-badge">Quiz results</span></div>
            <div class="results-context">Quiz Type : {{ $quiz->type }} · Final Score</div>
            <div class="result-grid">
                <section class="result-card score-card">
                    <div class="accuracy-wrap">
                        <div class="ring-box" aria-label="{{ $accuracy }} percent accuracy">
                            <svg class="accuracy-ring" viewBox="0 0 120 120" aria-hidden="true">
                                <circle class="track" cx="60" cy="60" r="52"></circle>
                                <circle class="value" cx="60" cy="60" r="52"
                                    style="--accuracy-offset: {{ 326.73 - (326.73 * $accuracy) / 100 }}"></circle>
                            </svg>
                            <div class="accuracy-number">{{ $accuracy }}%</div>
                        </div>
                        <div class="score-copy">
                            <div class="result-label">Quiz complete</div>
                            <h1>{{ $quiz->name ?: 'Medical quiz' }}</h1>
                            <p><strong>{{ $score }} / {{ $questionCount }}</strong> questions correct · <span
                                    class="text-success">{{ $accuracy >= 70 ? 'Great performance' : 'Keep practising' }}</span>
                            </p><button class="btn btn-primary" id="reviewDiagnostics" type="button"><span
                                    class="material-symbols-outlined align-middle me-1">visibility</span>Review
                                diagnostics</button>
                        </div>
                    </div>
                    <div class="response-bar" aria-label="Answer breakdown"><span
                            style="width: {{ $questionCount ? ($correctCount / $questionCount) * 100 : 0 }}%"></span><span
                            style="width: {{ $questionCount ? ($wrongCount / $questionCount) * 100 : 0 }}%"></span><span
                            style="width: {{ $questionCount ? ($unansweredCount / $questionCount) * 100 : 0 }}%"></span>
                    </div>
                    <div class="bar-legend"><span><b>{{ $accuracy }}% Correct ({{ $correctCount }})</b></span><span><b
                                class="danger">{{ $questionCount ? round(($wrongCount / $questionCount) * 100, 1) : 0 }}%
                                Incorrect
                                ({{ $wrongCount }})</b></span><span>{{ $questionCount ? round(($unansweredCount / $questionCount) * 100, 1) : 0 }}%
                            Unanswered ({{ $unansweredCount }})</span></div>
                </section>
                <section class="result-card telemetry">
                    <h2>Attempt summary <span class="results-badge">{{ strtoupper($attempt->status) }}</span></h2>
                    <div class="telemetry-stat">
                        <div class="text-secondary small">Final rating</div><strong>{{ $newRank ?? '—' }}</strong>
                        @if ($rankChange !== null)
                            <span
                                class="{{ $rankChange >= 0 ? 'text-success' : 'text-danger' }}">{{ $rankChange >= 0 ? '+' : '' }}{{ $rankChange }}
                                rating</span>
                        @endif
                        <div class="text-secondary small mono">
                            Previous: {{ $previousRank ?? '—' }}</div>
                    </div>
                    <div class="telemetry-list">
                        <div><span>Answered</span><strong>{{ $answeredCount }} / {{ $questionCount }}</strong></div>
                        <div><span>Total time</span><strong>{{ sprintf('%02d:%02d', $minutes, $seconds) }}</strong></div>
                        <div><span>Topic</span><strong>{{ $topic }}</strong></div>
                    </div>
                </section>
            </div>
            <section class="metric-strip" aria-label="Result statistics">
                <div class="metric good"><span class="material-symbols-outlined">check</span>
                    <div><strong>{{ $correctCount }}</strong><small>Correct</small></div>
                </div>
                <div class="metric bad"><span class="material-symbols-outlined">close</span>
                    <div><strong>{{ $wrongCount }}</strong><small>Incorrect</small></div>
                </div>
                <div class="metric"><span class="material-symbols-outlined">radio_button_unchecked</span>
                    <div><strong>{{ $unansweredCount }}</strong><small>Unanswered</small></div>
                </div>
                <div class="metric"><span class="material-symbols-outlined">percent</span>
                    <div><strong>{{ number_format($accuracy, 1) }}%</strong><small>Accuracy</small></div>
                </div>
                <div class="metric"><span class="material-symbols-outlined">timer</span>
                    <div><strong>{{ sprintf('%02d:%02d', $minutes, $seconds) }}</strong><small>Total time</small></div>
                </div>
            </section>
            <section id="questionReview">
                <div class="review-head">
                    <div>
                        <h2>Review questions</h2>
                        <p>Check your choices and review the explanations.</p>
                    </div>
                    <div class="review-filters" role="group" aria-label="Filter questions"><button
                            class="btn btn-sm active" data-filter="all">All ({{ $questionCount }})</button><button
                            class="btn btn-sm" data-filter="correct">Correct ({{ $correctCount }})</button><button
                            class="btn btn-sm" data-filter="incorrect">Incorrect ({{ $wrongCount }})</button><button
                            class="btn btn-sm" data-filter="unanswered">Unanswered ({{ $unansweredCount }})</button></div>
                </div>
                @forelse ($questions as $question)
                    @php($answer = $answersByQuestion->get($question->id))
                    @php($selected = $answer ? $question->options->firstWhere('id', $answer->option_id) : null)
                    @php($correct = $question->correctAnswer)
                    @php($status = !$answer ? 'unanswered' : ($answer->is_correct ? 'correct' : 'incorrect'))
                    <article class="review-card {{ $status }} {{ $status === 'incorrect' ? 'open' : '' }}"
                        data-status="{{ $status }}">
                        <div class="review-summary" role="button" tabindex="0"
                            aria-expanded="{{ $status === 'incorrect' ? 'true' : 'false' }}">
                            <div class="review-title"><span class="review-status"
                                    aria-hidden="true">{{ $status === 'correct' ? '✓' : ($status === 'incorrect' ? '×' : '○') }}</span><span
                                    class="mono text-secondary">{{ sprintf('%02d', $loop->iteration) }}</span>{!!   $question->name ?: Str::limit(strip_tags($question->content), 80) !!}
                            </div>
                            <div class="review-right"><span
                                    class="result-badge {{ $status }}">{{ ucfirst($status) }}</span><span
                                    class="material-symbols-outlined">{{ $status === 'incorrect' ? 'expand_less' : 'expand_more' }}</span>
                            </div>
                        </div>
                        <div class="review-body">
                            @if ($selected)
                                <p>Selected: <strong
                                        class="{{ $status === 'correct' ? 'text-success' : 'text-danger' }}">{{ $selected->name ?: $selected->content }}</strong>
                            </p>@else<p>Status: <strong>Not answered</strong></p>
                                @endif @if ($status !== 'correct' && $correct)
                                    <p>Correct answer: <strong
                                            class="text-success">{{ $correct->name ?: $correct->content }}</strong></p>
                                    @endif @if ($question->main_explanation)
                                        <div class="explanation">{!!   $question->main_explanation !!}</div>
                                    @endif
                        </div>
                    </article>
                @empty
                    <div class="empty-state">No questions were recorded for this quiz.</div>
                @endforelse
            </section>
            <div class="results-actions"><a class="btn btn-primary" href="#questionReview">Review answers</a><a
                    class="btn btn-outline-secondary" href="{{ route('start.quiz') }}">Back to quizzes</a></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/quiz-results.js') }}"></script>
@endpush
