<x-user-layout>
    @push('style')
        <link
            href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Space+Grotesk:wght@500;600;700&display=swap"
            rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
            rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('assets/css/gameResults.css') }}" />
    @endpush
    <div class="app-shell">
        <!-- Main Content -->
        <main class="main">
            <div class="content-wrap">
                <div class="columns">
                    <!-- Left Column -->
                    <section class="left-col">
                        <div class="summary-header">
                            <h2>BATTLE SUMMARY</h2>
                            <p class="match-id">Game ID: <span id="matchId">{{ $game->id }}</span></p>
                        </div>

                        <!-- Dual progress bar -->
                        <div class="dual-bar">
                            <div class="bar-fill bar-blue" id="barBlue" style="width: 55%"></div>
                            <div class="bar-fill bar-red" id="barRed" style="width: 45%"></div>
                            <div class="bar-vs"><span>VS</span></div>
                        </div>

                        <!-- Players -->
                        <div class="players">
                            @foreach ($attempts as $attempt)
                                <div class="player-card {{ $attempt->user->id === $winner->id ? 'winner' : 'loser' }}">
                                    <div class="victory-ribbon ">
                                        {{ $attempt->user->id === $winner->id ? 'WINNER' : 'LOSER' }}
                                    </div>
                                    @php
                                        $user = $attempt->user;
                                    @endphp
                                    <div class="player-top">
                                        <div class="avatar winner">{{ Str::limit($attempt->user->name, 1, '') }}</div>
                                        <div>
                                            <h3 class="player-name"></h3>
                                            <p class="player-elo up">
                                                <span
                                                    class="material-symbols-outlined">{{ Str::limit($user->name, 20) }}</span>
                                            </p>
                                        </div>
                                        {{-- <div class="player-score">
                                            <span class="label">ELO</span>
                                            <span class="value winner">    
                                                {{ $user->game_rank }}
                                            </span>
                                        </div> --}}
                                        <div class="player-score d-flex">
                                            <div class="">
                                                <div class="stat-card d-flex align-items-center  p-0 ">
                                                    <span style="color: var(--text)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="26"
                                                            height="26" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-brain">
                                                            <path d="M12 18V5" />
                                                            <path d="M15 13a4.17 4.17 0 0 1-3-4 4.17 4.17 0 0 1-3 4" />
                                                            <path d="M17.598 6.5A3 3 0 1 0 12 5a3 3 0 1 0-5.598 1.5" />
                                                            <path d="M17.997 5.125a4 4 0 0 1 2.526 5.77" />
                                                            <path d="M18 18a4 4 0 0 0 2-7.464" />
                                                            <path
                                                                d="M19.967 17.483A4 4 0 1 1 12 18a4 4 0 1 1-7.967-.517" />
                                                            <path d="M6 18a4 4 0 0 1-2-7.464" />
                                                            <path d="M6.003 5.125a4 4 0 0 0-2.526 5.77" />
                                                        </svg>
                                                    </span>
                                                    <span class="fs-4 px-2 pb-0 h" style="color: var(--text)">
                                                        {{ $attempt->current_rank }}</span>
                                                    <span
                                                        class="px-2 py-2 fs-6  
                        
                                                            {{ $attempt->current_rank > $attempt->new_rank ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }}">
                                                        {{ $attempt->new_rank - $attempt->current_rank }}
                                                        @if ($attempt->current_rank > $attempt->new_rank)
                                                            <i class="fa-solid fa-arrow-down"></i>
                                                        @else
                                                            <i class="fa-solid fa-arrow-up"></i>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="player-stats">
                                        <div class="stat-row shaded">
                                            CORRECT</span>
                                            <span class="stat-value correct">
                                                {{ $attempt->score }} / {{ $questions->count() }}</span>
                                        </div>
                                        <div class="stat-row">
                                            WRONG
                                            <span
                                                class="stat-value wrong">{{ $questions->count() - $attempt->score }}/20</span>
                                        </div>
                                        <div class="stat-row shaded">
                                            <span class="stat-label"><span
                                                    class="material-symbols-outlined">TIMER</span> AVG TIME / Q</span>
                                            <span
                                                class="stat-value">{{ round($questions->count() / $attempt->started_at->diffInSeconds($attempt->ended_at), 2) }}
                                                S</span>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                        <!-- Action buttons -->
                        <div class="actions">
                            <button class="btn btn-primary">REMATCH</button>
                            <button class="btn btn-outline">RETURN TO ARENA</button>
                        </div>
                    </section>

                    <!-- Right Column -->
                    <section class="right-col">
                        <h3 class="log-title">Game ANALYSIS</h3>

                        <div class="log-table-card">
                            <div class="log-table-header">
                                <span class="log-table-label">FULL MATCH LOG: Q<span id="qStart">1</span>-Q<span
                                        id="qEnd">{{ $questions->count() }}</span></span>
                                <div class="legend">
                                    @foreach ($attempts as $attempt)
                                        <span class="legend-item"><i class="dot dot-blue"></i>Player
                                            {{ $loop->iteration }} [<span
                                                id="p1Initial">{{ Str::limit($attempt->user->name, 1, '') }}</span>]</span>
                                    @endforeach
                                </div>
                            </div>

                            <livewire:game-results :questions="$questions" :attempts="$attempts" />
                        </div>
                </div>
                </section>
            </div>
    </div>
    </main>

    </div>
</x-user-layout>
