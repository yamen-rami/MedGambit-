<main class="profile-main">
    <section class="profile-hero">
        <div class="identity">
            <div class="profile-avatar">
                @if ($user->image)
                    <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}">
                @else
                    {{ $user->initials() }}
                @endif
                <span></span>
            </div>
            <div>
                <div class="name-line">
                    <h1>{{ $user->name }}</h1><span class="elo">{{ number_format($user->rank) }} ELO</span>
                    @if ($user->graduated)
                        <span class="rank">GRADUATED</span>
                    @endif
                </div>
                <p class="email">{{ $user->email }}</p>
                <div class="identity-meta">
                    @if ($user->country)
                        <span><i class="bi bi-geo-alt"></i> {{ $user->country }}</span><span>·</span>
                    @endif
                    @if ($user->created_at)
                        <span>Member since {{ $user->created_at->format('M Y') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="hero-actions">
            @if ($editing)
                <form wire:submit="updateProfile" class="profile-edit-form">
                    <input type="text" wire:model="name" placeholder="Name" aria-label="Name">
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                    <input type="email" wire:model="email" placeholder="Email" aria-label="Email">
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                    <button class="btn btn-primary" type="submit">Save</button>
                    <button class="btn btn-outline-secondary" type="button" wire:click="cancelEditing">Cancel</button>
                </form>
            @else
                <button class="btn btn-outline-secondary" type="button" wire:click="startEditing"><i
                        class="bi bi-pencil"></i> Edit profile</button>
            @endif
            <a class="btn btn-primary" href="{{ route('start.quiz') }}"><i class="bi bi-play-fill"></i> Start quiz</a>
        </div>
        <div class="profile-facts">
            @if ($user->country)
                <div><small>COUNTRY</small><strong>{{ $user->country }}</strong></div>
            @endif
            @if ($user->year && !$user->graduated)
                <div><small>ACADEMIC YEAR</small><strong>Year {{ $user->year }}</strong></div>
            @endif
            <div><small>STATUS</small><strong
                    class="text-primary">{{ $user->graduated ? 'Graduate' : 'Medical Student' }}</strong></div>
            @if ($user->gender)
                <div><small>GENDER</small><strong>{{ ucfirst($user->gender) }}</strong></div>
            @endif
        </div>
    </section>

    <section class="metric-grid">
        <article><span class="metric-icon blue"><i class="bi bi-graph-up-arrow"></i></span>
            <div><small>CLINICAL
                    RATING</small><strong>{{ number_format($user->rank) }}</strong><em>{{ $stats->completed }}
                    completed quizzes</em></div>
        </article>
        <article><span class="metric-icon green"><i class="bi bi-trophy"></i></span>
            <div><small>GLOBAL
                    RANK</small><strong>#{{ number_format($rankPosition) }}</strong><em>{{ $user->played_questions_count ?? 0 }}
                    questions played</em></div>
        </article>
        <article><span class="metric-icon amber"><i class="bi bi-lightning-charge"></i></span>
            <div><small>QUIZ
                    COMPLETION</small><strong>{{ $stats->total ? round(($stats->completed / $stats->total) * 100, 1) : 0 }}%</strong><em>{{ $stats->incomplete }}
                    in progress</em></div>
        </article>
        <article><span class="metric-icon purple"><i class="bi bi-bullseye"></i></span>
            <div><small>DIAGNOSTIC
                    ACCURACY</small><strong>{{ number_format($accuracy, 1) }}%</strong><em>{{ $answerStats->answered }}
                    answered questions</em></div>
        </article>
    </section>

    <section class="panel quiz-overview">
        <div class="panel-head">
            <div><span class="eyebrow">PERFORMANCE TELEMETRY</span>
                <h2>Quiz activity overview</h2>
            </div><span class="mono">LIVE DATA</span>
        </div>
        <div class="quiz-stats">
            <div><small>QUESTIONS
                    PLAYED</small><strong>{{ $user->played_questions_count ?? 0 }}</strong><em>{{ $answerStats->answered }}
                    answered</em></div>
            <div><small>COMPLETED QUIZZES</small><strong>{{ $stats->completed }}</strong><em
                    class="green-text">Finished</em></div>
            <div><small>INCOMPLETE QUIZZES</small><strong>{{ $stats->incomplete }}</strong><em>Resumable</em></div>
            <div><small>ACCURACY</small><strong
                    class="green-text">{{ number_format($accuracy, 1) }}%</strong><em>{{ $answerStats->correct }}
                    correct</em></div>
        </div>
    </section>

    <section class="panel battles-panel">
        <div class="panel-head">
            <div><span class="eyebrow">QUIZ HISTORY</span>
                <h2>Recent quizzes</h2>
            </div>
            <div class="profile-table-tools">
                <label class="profile-search"><i class="bi bi-search"></i><input type="search"
                        wire:model.live.debounce.300ms="quizSearch" placeholder="Search quiz name..."
                        aria-label="Search quiz name"></label>
                <select wire:model.live="quizFilter" aria-label="Filter quizzes">
                    <option value="all">All quizzes</option>
                    <option value="completed">Completed</option>
                    <option value="incomplete">Incomplete</option>
                </select>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>QUIZ</th>
                        <th>STATUS</th>
                        <th>SCORE</th>
                        <th>ANSWERS</th>
                        <th>DATE</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->filteredAttempts() as $attempt)
                        <tr>
                            <td>
                                <div class="opponent">
                                    <span>{{ strtoupper(substr($attempt['name'], 0, 1)) }}</span>
                                    <div>
                                        <strong>{{ $attempt['name'] }}</strong><small>{{ $attempt['topic'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><b
                                    class="result {{ $attempt['status'] === 'finished' ? 'win' : 'draw' }}">{{ ucfirst($attempt['status']) }}</b>
                            </td>
                            <td>{{ $attempt['score'] }}</td>
                            <td>{{ $attempt['correct_answers'] }} / {{ $attempt['answers'] }}</td>
                            <td>{{ $attempt['created_at'] }}</td>
                            <td>
                                <a class="btn btn-outline-primary"
                                    href="
                                                    @if ($attempt['quiz_type'] == 'random') {{ route('start.random.quiz', $attempt['quiz_id']) }}
                                                    @elseif($attempt['quiz_type'] == 'detected')
                                                          {{ route('start.detecated.quiz', $attempt['quiz_id']) }}
                                                    @else {{ route('start.learning.quiz', $attempt['quiz_id']) }} @endif
                                                                    ">View
                                    Your Attempt</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">{{ $quizSearch ? 'No quizzes match your search.' : 'No quizzes yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel battles-panel">
        <div class="panel-head">
            <div><span class="eyebrow">GAME HISTORY</span>
                <h2>Recent games</h2>
            </div>
            <select wire:model.live="gameFilter" aria-label="Filter games">
                <option value="all">All games</option>
                <option value="winning">Winning</option>
                <option value="losing">Losing</option>
            </select>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>GAME</th>
                        <th>OPPONENT</th>
                        <th>RESULT</th>
                        <th>SCORE</th>
                        <th>STATUS</th>
                        <th>DATE</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->filteredGames() as $gameAttempt)
                        <tr>
                            <td>
                                <div class="opponent"><span>G</span>
                                    <div><strong>Game
                                            #{{ $gameAttempt['game_id'] }}</strong><small>{{ ucfirst($gameAttempt['difficulty']) }}
                                            game</small></div>
                                </div>
                            </td>
                            <td>
                                <div class="opponent">
                                    <span>{{ strtoupper(substr($gameAttempt['opponent_name'], 0, 1)) }}</span>
                                    <div>
                                        <strong>{{ $gameAttempt['opponent_name'] }}</strong><small>{{ $gameAttempt['opponent_email'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><b
                                    class="result {{ $gameAttempt['is_winner'] ? 'win' : 'draw' }}">{{ $gameAttempt['is_winner'] ? 'Winner' : 'Not winner' }}</b>
                            </td>
                            <td>{{ $gameAttempt['score'] }}</td>
                            <td>{{ ucfirst($gameAttempt['status']) }}</td>
                            <td>{{ $gameAttempt['created_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">{{ $gameFilter === 'all' ? 'No games yet.' : 'No matching games.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>
