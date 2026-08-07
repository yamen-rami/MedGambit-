<x-user-layout>
    <!-- Navbar -->



    <!-- / Navbar -->

    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="row">
                <div class="col-12">
                    <div class="card mb-6">
                        <div class="user-profile-header-banner">
                            {{-- <img src="../../assets/img/pages/profile-banner.png" alt="Banner image"
                                class="rounded-top" /> --}}
                        </div>
                        <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-5">
                            <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                                <img  width="140px " src="{{ asset($user->image ?? '../../assets/img/avatars/1.png') }}"
                                    alt="user image" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" />
                            </div>
                            <div class="flex-grow-1 mt-3 mt-lg-5">
                                <div
                                    class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                                    <div class="user-profile-info">
                                        <h4 class="mb-2 mt-lg-6">{{ $user->name }}</h4>
                                        <ul
                                            class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                                            <li class="list-inline-item d-flex gap-2 align-items-center">
                                                <i class="icon-base ti tabler-palette icon-lg"></i><span
                                                    class="fw-medium">{{ $user->year !== null ? 'Medical Student' : 'Doctor' }}</span>
                                            </li>
                                            <li class="list-inline-item d-flex gap-2 align-items-center">
                                                <i class="icon-base ti tabler-map-pin  icon-lg"></i><span
                                                    class="fw-medium">{{ $user->location }}</span>
                                            </li>
                                            <li class="list-inline-item d-flex gap-2 align-items-center">
                                                <i class="icon-base ti tabler-calendar  icon-lg"></i><span
                                                    class="fw-medium">{{ $user->created_at }}</span>
                                            </li>
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
                        <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-sm-0 gap-2">
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
                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-flag icon-lg"></i><span
                                        class="fw-medium mx-2">Country:</span>
                                    <span>{{ $user->location }}</span>
                                </li>

                            </ul>
                            <p class="card-text text-uppercase text-body-secondary small mb-0">Contacts</p>
                            <ul class="list-unstyled my-3 py-1">
                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-phone-call icon-lg"></i><span
                                        class="fw-medium mx-2">Contact:</span>
                                    <span>{{ $user->phone }}</span>
                                </li>

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
                                    <span>{{ $user->attempts->where('status', 'completed')->count() }}</span>
                                </li>
                                <li class="d-flex align-items-center mb-4">
                                    <i class="icon-base ti tabler-layout-grid icon-lg"></i><span
                                        class="fw-medium mx-2">Quizzez In
                                        Completed</span> <span>
                                        {{ $user->attempts->where('status', 'pending')->count() }}</span>
                                </li>
                                <li class="d-flex align-items-center">
                                    <i class="icon-base ti tabler-users icon-lg"></i><span class="fw-medium mx-2">Played
                                        Quizzez</span>
                                    <span>{{ $user->attempts->count() }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!--/ Profile Overview -->
                </div>
                @if (auth()->id() == $user->id)
                    <div class="card col-lg-8 col-md-12 col-sm-12">
                        <table class="table">
                            <div class="col-lg-12 ">
                                <p class="pb-0 fw-bold py-6">
                                    Filtering
                                </p>
                            </div>
                            <thead class="my-5">
                                <tr class="">
                                    <th>Quiz Type</th>
                                    <th>Time Taken </th>
                                    <th>Correct Answers</th>
                                    <th>Wrong Answers</th>
                                    <th>Re Attempt</th>
                                </tr>
                            </thead>
                            @foreach ($user->attempts as $attempt)
                                <tbody>
                                    <td class="fw-bold">{{ $attempt->quiz->type }}</td>
                                    <td class="fw-bold">{{ floor($attempt->time_taken / 60) }}</td>
                                    <td class="fw-bold">{{ $attempt->answers->where('is_correct', true)->count() }}
                                    </td>
                                    <td class="fw-bold">{{ $attempt->answers->where('is_correct', false)->count() }}
                                    </td>
                                    <td class="fw-bold"><a class="btn btn-outline-warning" href="
                                                    @if ($attempt->quiz->type == 'random') {{ route('start.random.quiz', $attempt->quiz) }}
                                                    @elseif($attempt->quiz->type == 'detected')
                                                          {{ route('start.detecated.quiz', $attempt->quiz) }}
                                                    @else {{ route('start.learning.quiz', $attempt->quiz) }} @endif                  
                                                                    ">Re
                                            Attempt</a></td>
                                </tbody>
                            @endforeach
                        </table>
                @endif
                </div>
            </div>
            <!--/ User Profile Content -->
        </div>

        <div class="content-backdrop fade"></div>
    </div>
    <!-- Content wrapper -->

</x-user-layout>