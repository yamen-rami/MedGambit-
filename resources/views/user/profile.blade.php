<x-user-layout>
    <style>
        .profile-history-table {
            width: 100%;
        }

        .profile-history-table th,
        .profile-history-table td {
            vertical-align: middle;
        }

        .profile-history-table .quiz-name {
            min-width: 150px;
            white-space: normal;
        }

        .profile-history-table .quiz-action {
            min-width: 170px;
            white-space: normal;
        }

        .profile-history-mobile {
            display: none;
        }

        @media (max-width: 767.98px) {
            .profile-history-desktop {
                display: none;
            }

            .profile-history-mobile {
                display: block;
            }

            .profile-history-mobile .attempt-card {
                border: 1px solid var(--bs-border-color);
                border-radius: .5rem;
                padding: 1rem;
            }

            .profile-history-mobile .attempt-card+.attempt-card {
                margin-top: .75rem;
            }
        }
    </style>
    <!-- Navbar -->

    <!-- / Navbar -->

    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl container-p-y flex-grow-1">
            <!-- Header -->
            <div class="row">
                <div class="col-12">
                    <div class="card mb-6">
                        <div class="user-profile-header-banner">
                            {{-- <img src="../../assets/img/pages/profile-banner.png" alt="Banner image"
                                class="rounded-top" /> --}}
                        </div>
                        <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start mb-5 text-center">
                            <div class="mt-n2 mx-sm-0 mx-auto flex-shrink-0">
                                <img width="140px " src="{{ asset($user->image ?? '../../assets/img/avatars/1.png') }}"
                                    alt="user image" class="d-block ms-sm-6 user-profile-img ms-0 h-auto rounded" />
                            </div>
                            <div class="mt-lg-5 mt-3 flex-grow-1">
                                <div
                                    class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start flex-md-row flex-column mx-5 gap-4">
                                    <div class="user-profile-info">
                                        <h4 class="mt-lg-6 mb-2">{{ $user->name }}</h4>
                                        <ul
                                            class="list-inline d-flex align-items-center justify-content-sm-start justify-content-center my-2 mb-0 flex-wrap gap-4">
                                            <li class="list-inline-item d-flex align-items-center gap-2">
                                                <i class="icon-base ti tabler-palette icon-lg"></i><span
                                                    class="fw-medium">{{ $user->year !== null ? 'Medical Student' : 'Doctor' }}</span>
                                            </li>
                                            <li class="list-inline-item d-flex align-items-center gap-2">
                                                <i class="icon-base ti tabler-map-pin icon-lg"></i><span
                                                    class="fw-medium">{{ $user->country ?? '—' }}</span>
                                            </li>

                                            <li class="d-flex align-items-center my-4"><i
                                                    class="icon-base ti tabler-target icon-lg"></i><span
                                                    class="fw-medium mx-2">Accuracy:</span><span>{{ $answerStats->answered ?? 0 ? round(($answerStats->correct / $answerStats->answered) * 100, 1) : 0 }}%</span>
                                            </li>
                                            <li class="d-flex align-items-center my-4"><i
                                                    class="icon-base ti tabler-trophy icon-lg"></i><span
                                                    class="fw-medium mx-2">Rank:</span><span>#{{ $rankPosition }}
                                                    ({{ $user->rank }})</span></li>

                                        </ul>
                                    </div>
                                    <a href="javascript:void(0)" class="btn btn-primary mb-1">
                                        <i class="icon-base ti tabler-user-check icon-xs me-2"></i>Connected
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Header -->

            <!-- Navbar pills -->
            <div class="row">
                <div class="col-md-12">
                    <div class="nav-align-top">
                        <ul class="nav nav-pills flex-column flex-sm-row gap-sm-0 mb-6 gap-2">
                            <li class="nav-item">
                                <a class="nav-link active" href="javascript:void(0);"><i
                                        class="icon-base ti tabler-user-check icon-sm me-1_5"></i> Profile</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--/ Navbar pills -->

            <!-- User Profile Content -->
            <div class="row">
                <div class="col-xl-4 col-lg-5 col-md-5">
                    <!-- About User -->
                    <div class="card mb-6">
                        <div class="card-body">
                            <p class="card-text text-uppercase text-body-secondary small mb-0">About</p>
                            <ul class="list-unstyled my-3 py-1">
                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-user icon-lg"></i><span class="fw-medium mx-2">Full
                                        Name:</span>
                                    <span>{{ $user->name }}</span>
                                </li>
                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-check icon-lg"></i><span
                                        class="fw-medium mx-2">Status:</span>
                                    <span>Active</span>
                                </li>
                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-crown icon-lg"></i><span
                                        class="fw-medium mx-2">Role:</span>
                                    <span>{{ $user->year !== null ? 'Medical Student' : 'Doctor' }}</span>
                                </li>
                                <li class="list-inline-item d-flex align-items-center gap-2 my-4">
                                    <span>
                                        <i class="icon-base ti tabler-calendar icon-lg"></i>
                                        Since: 
                                    </span>
                                    <span class="fw-medium">{{ (int) $user->created_at->diffInDays(now()) }}</span>
                                </li>

                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-flag icon-lg"></i><span
                                        class="fw-medium mx-2">Country:</span>
                                    <span>{{ $user->country ?? '—' }}</span>
                                </li>
                            </ul>
                            <p class="card-text text-uppercase text-body-secondary small mb-0">Contacts</p>
                            <ul class="list-unstyled my-3 py-1">
                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-phone-call icon-lg"></i><span
                                        class="fw-medium mx-2">Contact:</span>
                                    <span>{{ $user->phone }}</span>
                                </li>
                                @if (!$user->graduated)
                                    <li class="d-flex align-items-center mb-4"><i
                                            class="icon-base ti tabler-school icon-lg"></i><span
                                            class="fw-medium mx-2">Year:</span><span>{{ $user->year ?? '—' }}</span>
                                    </li>
                                @endif

                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-mail icon-lg"></i><span
                                        class="fw-medium mx-2">Email:</span>
                                    <span>{{ $user->email }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!--/ About User -->
                    <!-- Profile Overview -->
                    <div class="card mb-6">
                        <div class="card-body">
                            <p class="card-text text-uppercase text-body-secondary small">Overview</p>
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-check icon-lg"></i><span
                                        class="fw-medium mx-2">Quizzes Completed</span>
                                    <span>{{ $stats->completed ?? 0 }}</span>
                                </li>
                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-layout-grid icon-lg"></i><span
                                        class="fw-medium mx-2">Quizzez In Completed</span>
                                    <span>{{ $stats->incomplete ?? 0 }}</span>
                                </li>
                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-users icon-lg"></i><span class="fw-medium mx-2">Played
                                        Quizzez</span>
                                    <span>{{ $stats->total ?? 0 }}</span>
                                </li>

                                <li class="d-flex align-items-center"><i
                                        class="icon-base ti tabler-help icon-lg"></i><span class="fw-medium mx-2">Played
                                        Questions:</span><span>{{ $user->played_questions_count }}</span></li>
                            </ul>
                        </div>
                    </div>
                    <!--/ Profile Overview -->
                </div>
                <div class="card col-lg-8 col-md-12 col-sm-12">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                            <h5 class="mb-0">Quiz history</h5><span
                                class="text-body-secondary small">{{ $attempts->total() }} attempts</span>
                        </div>
                        <form class="row g-2 mb-4" method="GET">
                            <div class="col-12 col-md-5"><label class="form-label" for="quiz-search">Search
                                    quizzes</label><input id="quiz-search" name="search" value="{{ $search }}"
                                    class="form-control" placeholder="Quiz name"></div>
                            <div class="col-6 col-md-3"><label class="form-label"
                                    for="quiz-filter">Status</label><select id="quiz-filter" name="filter"
                                    class="form-select">
                                    <option value="all" @selected($filter === 'all')>All attempts</option>
                                    <option value="completed" @selected($filter === 'completed')>Completed</option>
                                    <option value="incomplete" @selected($filter === 'incomplete')>Incomplete</option>
                                </select></div>
                            <div class="col-6 col-md-3"><label class="form-label" for="quiz-sort">Sort
                                    by</label><select id="quiz-sort" name="sort" class="form-select">
                                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                                    <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                                    <option value="highest" @selected($sort === 'highest')>Highest score</option>
                                    <option value="lowest" @selected($sort === 'lowest')>Lowest score</option>
                                </select></div>
                            <div class="col-12 col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100"
                                    aria-label="Apply filters"><i class="icon-base ti tabler-search"></i></button>
                            </div>
                        </form>
                        <div class="table-responsive profile-history-desktop">
                            <table class="table align-middle profile-history-table">
                                <thead class="my-5">
                                    <tr class="">
                                        <th class="quiz-name">Quiz</th>
                                        <th>Type</th>
                                        <th>Score</th>
                                        <th>Correct / Wrong</th>
                                        <th>Time Taken</th>
                                        <th class="quiz-action">Action</th>
                                    </tr>
                                </thead>
                                @foreach ($attempts as $attempt)
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold quiz-name">{{ $attempt->quiz->name }}</td>
                                            <td>{{ $attempt->quiz->type }}</td>
                                            <td class="fw-bold">{{ $attempt->score }} /
                                                {{ $attempt->quiz->questions_number }}</td>
                                            <td class="fw-bold">{{ $attempt->correct_answers_count }} /
                                                {{ $attempt->wrong_answers_count }}</td>
                                            <td class="fw-bold">
                                                {{ $attempt->time_taken ? floor($attempt->time_taken / 60) . ' min' : '—' }}
                                            </td>
                                            <td class="fw-bold quiz-action">
                                                @if ($attempt->status === 'finished')
                                                    <a class="btn btn-outline-primary"
                                                        href="{{ route('quizResult', $attempt->quiz) }}">View Your
                                                        Attempt</a>
                                                @else
                                                    <a class="btn btn-outline-warning"
                                                        href="{{ $attempt->quiz->type === 'random' ? route('random.quiz', $attempt->quiz) : ($attempt->quiz->type === 'detected' ? route('start.detecated.quiz', $attempt->quiz) : route('start.learning.quiz', $attempt->quiz)) }}">Complete
                                                        your attempt</a>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                @endforeach
                            </table>
                        </div>
                        <div class="profile-history-mobile">
                            @forelse ($attempts as $attempt)
                                <article class="attempt-card">
                                    <div class="d-flex justify-content-between gap-2 mb-3">
                                        <strong class="text-break">{{ $attempt->quiz->name }}</strong>
                                        <span
                                            class="badge {{ $attempt->status === 'finished' ? 'bg-label-success' : 'bg-label-warning' }}">{{ $attempt->status === 'finished' ? 'Completed' : 'Incomplete' }}</span>
                                    </div>
                                    <div class="row g-3 small mb-3">
                                        <div class="col-6"><span
                                                class="text-body-secondary d-block">Type</span>{{ ucfirst($attempt->quiz->type) }}
                                        </div>
                                        <div class="col-6"><span
                                                class="text-body-secondary d-block">Score</span>{{ $attempt->score }}
                                            / {{ $attempt->quiz->questions_number }}</div>
                                        <div class="col-6"><span class="text-body-secondary d-block">Correct /
                                                Wrong</span>{{ $attempt->correct_answers_count }} /
                                            {{ $attempt->wrong_answers_count }}</div>
                                        <div class="col-6"><span class="text-body-secondary d-block">Time
                                                taken</span>{{ $attempt->time_taken ? floor($attempt->time_taken / 60) . ' min' : '—' }}
                                        </div>
                                    </div>
                                    @if ($attempt->status === 'finished')
                                        <a class="btn btn-outline-primary w-100"
                                            href="{{ route('quizResult', $attempt->quiz) }}">View Your Attempt</a>
                                    @else
                                        <a class="btn btn-outline-warning w-100"
                                            href="{{ $attempt->quiz->type === 'random' ? route('random.quiz', $attempt->quiz) : ($attempt->quiz->type === 'detected' ? route('start.detecated.quiz', $attempt->quiz) : route('start.learning.quiz', $attempt->quiz)) }}">Complete
                                            your attempt</a>
                                    @endif
                                </article>
                            @empty
                                <p class="text-body-secondary text-center py-4 mb-0">No quiz attempts found.</p>
                            @endforelse
                        </div>
                        <div class="d-flex justify-content-center mt-3">{{ $attempts->links() }}</div>
                    </div>
                </div>
            </div>
            <!--/ User Profile Content -->
        </div>

        <div class="content-backdrop fade"></div>
    </div>
    <!-- Content wrapper -->
</x-user-layout>
