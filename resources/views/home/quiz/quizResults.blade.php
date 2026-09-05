<x-user-layout>
    <x-slot:title>Quiz Result</x-slot:title>
    @push('style')
        <link rel="stylesheet" href="{{ asset('assets/css/quiz-result.css') }}" />
    @endpush

    <div class="results-page" x-data="{ all: true, correct: false, incorrect: false }">
        <!-- Header -->
        <header class="results-header">
            <div>
                <h1>Quiz Type : {{ $quiz->type }}</h1>
                <p>{{ $quiz->updated_at }}</p>
            </div>

            <div class="header-actions">
                <div class="stat-card ">
                    <p style="color: var(--text)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brain">
                            <path d="M12 18V5" />
                            <path d="M15 13a4.17 4.17 0 0 1-3-4 4.17 4.17 0 0 1-3 4" />
                            <path d="M17.598 6.5A3 3 0 1 0 12 5a3 3 0 1 0-5.598 1.5" />
                            <path d="M17.997 5.125a4 4 0 0 1 2.526 5.77" />
                            <path d="M18 18a4 4 0 0 0 2-7.464" />
                            <path d="M19.967 17.483A4 4 0 1 1 12 18a4 4 0 1 1-7.967-.517" />
                            <path d="M6 18a4 4 0 0 1-2-7.464" />
                            <path d="M6.003 5.125a4 4 0 0 0-2.526 5.77" />
                        </svg>
                    </p>
                    <p class="fs-2" style="color: var(--text)">{{ $attempt->current_rank }}</p>
                    <span
                        class="px-2 py-2 fs-6  
                        
                        {{ $attempt->current_rank > $attempt->new_rank ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }}"
                    >
                        {{ $attempt->new_rank - $attempt->current_rank }}
                        @if ($attempt->current_rank > $attempt->new_rank)
                            <i class="fa-solid fa-arrow-down"></i>
                        @else
                            <i class="fa-solid fa-arrow-up"></i>
                        @endif
                    </span>
                </div>
            </div>
        </header>

        <!-- Stats -->
        <section class="stats-grid">
            <div class="score-card">
                <span class="card-label">Final Score</span>
                <strong class="score-value {{ $quiz->questions->count() / 2 <= $attempt->score ? 'text-success' : 'text-danger' }}">{{ $attempt->score }} <span class="text-white"> / {{ $quiz->questions->count() }}</strong>
                </span>
                @if ($quiz->questions->count() / 2 <= $attempt->score)
                    <span class="score-status">Excellent Work!</span>
                @else
                    <span class="score-status">You Have Failed</span>
                    <span>hard Lock </span>

                @endif
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div>
                    <span>Correct Answers</span>
                    <strong>{{ $correctAnswers->count() }} Questions</strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>

                <div>
                    <span>Incorrect</span>
                    <strong>{{ $wrongAnswers->count() }} Questions</strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fa-solid fa-layer-group"></i>
                </div>

                <div>
                    <span>Questions</span>
                    <strong>{{ $quiz->questions->count() }} Questions </strong>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-regular fa-clock"></i>
                </div>

                <div>
                    <span>Time Taken</span>
                    @php
                        $result = Number::format($attempt->time_taken / 60, precision: 2);
                    @endphp
                    <strong>{{ $result }} Minutes </strong>
                </div>
            </div>
        </section>

        <!-- Questions Header -->
        <div class="questions-heading">
            <div>
                <h2>Question Review</h2>
                <span>Review your answers and explanations</span>
            </div>

            <div class="filter-buttons">
                <button class="filter-btn active" @click="((all = true), (correct = false), (incorret = false))">
                    All
                </button>
                <button class="filter-btn correct" @click="((all = false), (correct = true), (incorrect = false))">
                    Correct
                </button>
                <button class="filter-btn incorrect" @click="((all = false), (correct = false), (incorrect = true))">
                    Incorrect
                </button>
            </div>
        </div>

        <!-- Question 1 -->
        <div x-show="all">
            @foreach ($answers as $answer)
                <article class="question-card {{ $answer->question->correctAnswer->id == $answer->option_id ? 'correct-question' : 'incorrect-question' }}">
                    <div class="question-top">
                        <div class="question-number">{{ $loop->iteration }}</div>

                        <div class="question-content">
                            <p class="question-text">{{ $answer->question->content }}</p>
                        </div>
                        @if ($answer->question->correctAnswer->id == $answer->option_id)
                            <span class="result-badge correct-badge">
                                <i class="fa-solid fa-check"></i>
                                Correct
                            </span>
                        @else
                            <span class="result-badge incorrect-badge">
                                <i class="fa-regular fa-circle-xmark"></i>
                                Wrong
                            </span>
                        @endif
                    </div>

                    <div class="answers">
                        @foreach ($answer->question->options as $option)
                            @php
                                $correct = $answer->question->correctAnswer->id;
                            @endphp
                            <div
                                data-bs-toggle="modal"
                                data-bs-target="#staticBackdrop{{ $option->id }}"
                                class="answer selected  
                                                                                                                                                                @if ($correct == $option->id) correct-answer
                                                                                                                                                                @else
                                                                                                                                                                    @if ($answer->option_id == $option->id)
                                                                                                                                                                        @if ($answer->option_id == $correct)
                                                                                                                                                                            correct-answer
                                                                                                                                                                        @else
                                                                                                                                                                            wrong-answer @endif
                                                                                                                                                                    @endif
                                                                                                                                                                @endif
                                                                                                                                                                                                                        "
                            >
                                <span class="answer-letter">
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

                                <span class="answer-text"> {{ $option->content }} </span>
                                @if ($correct == $option->id)
                                    <i class="fa-solid fa-check answer-icon"></i>
                                @else
                                    <i class="fa-regular fa-circle-xmark text-danger"></i>
                                @endif

                                <!-- Button trigger modal -->
                                <!-- Large Modal -->
                                <!--
                                                                        Large Modal
                                                                      <div class="modal-dialog modal-xl">...</div>
                                                                        Meduim Modal
                                                                      <div class="modal-dialog modal-lg">...</div>
                                                                        Small Modal
                                                                      <div class="modal-dialog modal-sm">...</div>
                                                                    -->

                                <!-- Modal -->
                                <div
                                    class="modal fade modal-lg"
                                    id="staticBackdrop{{ $option->id }}"
                                    data-bs-backdrop="static"
                                    data-bs-keyboard="true"
                                    tabindex="-1"
                                    aria-labelledby="staticBackdropLabel"
                                    aria-hidden="true"
                                >
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                                                    Option Explanation
                                                </h1>
                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close"
                                                ></button>
                                            </div>
                                            <div class="modal-body">
                                                <h6>{{ $option->content }}</h6>
                                                <div class="d-flex align-items-center gap-1">
                                                    <h5>Explanation :</h5>
                                                    <p class="pb-0">{{ $option->explanation }}</p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Close
                                                </button>
                                                <button type="button" class="btn btn-primary">Understood</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>

        <div x-show="correct">
            @foreach ($correctAnswers as $answer)
                <article class="question-card {{ $answer->question->correctAnswer->id == $answer->option_id ? 'correct-question' : 'incorrect-question' }}">
                    <div class="question-top">
                        <div class="question-number">{{ $loop->iteration }}</div>

                        <div class="question-content">
                            <p class="question-text">{{ $answer->question->content }}</p>
                        </div>
                        @if ($answer->question->correctAnswer->id == $answer->option_id)
                            <span class="result-badge correct-badge">
                                <i class="fa-solid fa-check"></i>
                                Correct
                            </span>
                        @else
                            <span class="result-badge incorrect-badge">
                                <i class="fa-regular fa-circle-xmark"></i>
                                Wrong
                            </span>
                        @endif
                    </div>

                    <div class="answers">
                        @foreach ($answer->question->options as $option)
                            @php
                                $correct = $answer->question->correctAnswer->id;
                            @endphp
                            <div
                                data-bs-toggle="modal"
                                data-bs-target="#staticBackdrop{{ $option->id }}"
                                class="answer selected  
                                                                                                                                                                @if ($correct == $option->id) correct-answer
                                                                                                                                                                @else
                                                                                                                                                                    @if ($answer->option_id == $option->id)
                                                                                                                                                                        @if ($answer->option_id == $correct)
                                                                                                                                                                            correct-answer
                                                                                                                                                                        @else
                                                                                                                                                                            wrong-answer @endif
                                                                                                                                                                    @endif
                                                                                                                                                                @endif
                                                                                                                                                                                                                        "
                            >
                                <span class="answer-letter">
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

                                <span class="answer-text"> {{ $option->content }} </span>
                                @if ($correct == $option->id)
                                    <i class="fa-solid fa-check answer-icon"></i>
                                @else
                                    <i class="fa-regular fa-circle-xmark text-danger"></i>
                                @endif

                                <!-- Button trigger modal -->
                                <!-- Large Modal -->
                                <!--
                                                                        Large Modal
                                                                      <div class="modal-dialog modal-xl">...</div>
                                                                        Meduim Modal
                                                                      <div class="modal-dialog modal-lg">...</div>
                                                                        Small Modal
                                                                      <div class="modal-dialog modal-sm">...</div>
                                                                    -->

                                <!-- Modal -->
                                <div
                                    class="modal fade modal-lg"
                                    id="staticBackdrop{{ $option->id }}"
                                    data-bs-backdrop="static"
                                    data-bs-keyboard="true"
                                    tabindex="-1"
                                    aria-labelledby="staticBackdropLabel"
                                    aria-hidden="true"
                                >
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                                                    Option Explanation
                                                </h1>
                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close"
                                                ></button>
                                            </div>
                                            <div class="modal-body">
                                                <h6>{{ $option->content }}</h6>
                                                <div class="d-flex align-items-center gap-1">
                                                    <h5>Explanation :</h5>
                                                    <p class="pb-0">{{ $option->explanation }}</p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Close
                                                </button>
                                                <button type="button" class="btn btn-primary">Understood</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>

        <div x-show="incorrect">
            @foreach ($wrongAnswers as $answer)
                <article class="question-card {{ $answer->question->correctAnswer->id == $answer->option_id ? 'correct-question' : 'incorrect-question' }}">
                    <div class="question-top">
                        <div class="question-number">{{ $loop->iteration }}</div>

                        <div class="question-content">
                            <p class="question-text">{{ $answer->question->content }}</p>
                        </div>
                        @if ($answer->question->correctAnswer->id == $answer->option_id)
                            <span class="result-badge correct-badge">
                                <i class="fa-solid fa-check"></i>
                                Correct
                            </span>
                        @else
                            <span class="result-badge incorrect-badge">
                                <i class="fa-regular fa-circle-xmark"></i>
                                Wrong
                            </span>
                        @endif
                    </div>

                    <div class="answers">
                        @foreach ($answer->question->options as $option)
                            @php
                                $correct = $answer->question->correctAnswer->id;
                            @endphp
                            <div
                                data-bs-toggle="modal"
                                data-bs-target="#staticBackdrop{{ $option->id }}"
                                class="answer selected  
                                                                                                                                                                @if ($correct == $option->id) correct-answer
                                                                                                                                                                @else
                                                                                                                                                                    @if ($answer->option_id == $option->id)
                                                                                                                                                                        @if ($answer->option_id == $correct)
                                                                                                                                                                            correct-answer
                                                                                                                                                                        @else
                                                                                                                                                                            wrong-answer @endif
                                                                                                                                                                    @endif
                                                                                                                                                                @endif
                                                                                                                                                                                                                        "
                            >
                                <span class="answer-letter">
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

                                <span class="answer-text"> {{ $option->content }} </span>
                                @if ($correct == $option->id)
                                    <i class="fa-solid fa-check answer-icon"></i>
                                @else
                                    <i class="fa-regular fa-circle-xmark text-danger"></i>
                                @endif

                                <!-- Button trigger modal -->
                                <!-- Large Modal -->
                                <!--
                                                                        Large Modal
                                                                      <div class="modal-dialog modal-xl">...</div>
                                                                        Meduim Modal
                                                                      <div class="modal-dialog modal-lg">...</div>
                                                                        Small Modal
                                                                      <div class="modal-dialog modal-sm">...</div>
                                                                    -->

                                <!-- Modal -->
                                <div
                                    class="modal fade modal-lg"
                                    id="staticBackdrop{{ $option->id }}"
                                    data-bs-backdrop="static"
                                    data-bs-keyboard="true"
                                    tabindex="-1"
                                    aria-labelledby="staticBackdropLabel"
                                    aria-hidden="true"
                                >
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                                                    Option Explanation
                                                </h1>
                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close"
                                                ></button>
                                            </div>
                                            <div class="modal-body">
                                                <h6>{{ $option->content }}</h6>
                                                <div class="d-flex align-items-center gap-1">
                                                    <h5>Explanation :</h5>
                                                    <p class="pb-0">{{ $option->explanation }}</p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Close
                                                </button>
                                                <button type="button" class="btn btn-primary">Understood</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
        {{-- <div x-show="incorrect"> --}}

        <!-- Question 3 -->
        @if ($unanswered->count() != 0)
            @foreach ($unanswered as $un)
                <article class="question-card unanswered-question">
                    <div class="question-">
                        <div class="question-number">{{ $loop->iteration }}</div>

                        <div class="question-content">
                            <p class="question-text">{{ $un->content }}</p>
                        </div>
                        <span class="result-badge unanswered-badge">
                            <i class="fa-regular fa-circle"></i>
                            Unanswered
                        </span>
                    </div>
                    @foreach ($un->options as $option)
                        <div class="answers my-3">
                            <div class="answer disabled-answer">
                                <span class="answer-letter">
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

                                <span class="answer-text"> {{ $option->content }} </span>
                            </div>
                        </div>
                    @endforeach
                </article>
            @endforeach
        @endif
    </div>
</x-user-layout>
