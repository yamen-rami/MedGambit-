@push('styles')
    <style>
        .admin-dashboard .stat-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: .65rem;
            color: var(--bs-primary);
            background: rgba(115, 103, 240, .14)
        }

        .admin-dashboard .card {
            transition: transform .2s ease, box-shadow .2s ease
        }

        .admin-dashboard .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .12)
        }

        .admin-dashboard .table>:not(caption)>*>* {
            background: transparent;
            color: var(--bs-body-color);
            border-bottom-color: var(--bs-border-color)
        }
    </style>
@endpush

<x-app>
    <x-slot:title>Dashboard</x-slot:title>

    <main class="admin-dashboard container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <p class="text-body-secondary mb-1">Overview</p>
                <h1 class="h3 mb-0">Welcome back, {{ auth()->user()->name ?? 'Admin' }} 👋</h1>
            </div>
            <span class="text-body-secondary small">MedGambit Administration</span>
        </div>
{{-- ['Online', $counts['onlineUsers'], ''], --}}
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-body-secondary mb-2">Total Online Players</p>
                                <h2 class="mb-0">
                                        <x-online-users></x-online-users>
                                </h2>
                            </div>
                            <span class="stat-icon"><i class="icon-base ti tabler-wifi fs-4"></i></span>
                        </div>
                    </div>
                </div>
            @foreach ([ ['Users', $counts['users'], 'tabler-users'], ['Questions', $counts['questions'], 'tabler-help-circle'], ['Quizzes', $counts['quizzes'], 'tabler-clipboard-list'], ['Branches', $counts['branches'], 'tabler-git-branch'], ['Specialities', $counts['specialities'], 'tabler-school'], ['Skills', $counts['skills'], 'tabler-bulb']] as [$label, $count, $icon])
                <div class="col-sm-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-body-secondary mb-2">Total {{ $label }}</p>
                                <h2 class="mb-0">
                                        {{ number_format($count) }}
                                </h2>
                            </div>
                            <span class="stat-icon"><i class="icon-base ti {{ $icon }} fs-4"></i></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <section class="mb-4">
            <h4 class="mb-3">Quick Actions</h4>
            <div class="row g-3">
                @foreach ([['Create question', route('questions.create'), 'tabler-plus'], ['Browse quizzes', route('quizez.index'), 'tabler-stethoscope'], ['Manage users', route('users'), 'tabler-users']] as [$label, $url, $icon])
                    <div class="col-md-4">
                        <a class="card text-decoration-none h-100" href="{{ $url }}">
                            <div class="card-body d-flex align-items-center gap-3">
                                <span class="stat-icon"><i class="icon-base ti {{ $icon }}"></i></span>
                                <span class="fw-medium text-body">{{ $label }}</span>
                                <i class="icon-base ti tabler-arrow-up-right ms-auto text-body-secondary"></i>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="row g-4">
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-1">Recent Users</h5>
                        <p class="text-body-secondary mb-0">Latest accounts added</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <tbody>
                                @forelse ($dashboardUsers as $user)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-medium">{{ $user->name }}</div><small
                                                class="text-body-secondary">{{ $user->email }}</small>
                                        </td>
                                        <td class="text-end pe-4 text-body-secondary small">
                                            {{ $user->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="p-4 text-body-secondary">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-1">Recent Questions</h5>
                        <p class="text-body-secondary mb-0">Latest clinical content</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <tbody>
                                @forelse ($dashboardQuestions as $question)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-medium">{{ $question->name ?: 'Untitled question' }}</div>
                                            <small class="text-body-secondary d-block text-truncate"
                                                style="max-width: 330px">{!! $question->content !!}</small>
                                        </td>
                                        <td class="text-end pe-4 text-body-secondary small">
                                            {{ $question->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="p-4 text-body-secondary">No questions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-app>
