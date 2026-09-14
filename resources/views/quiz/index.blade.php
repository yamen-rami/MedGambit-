<x-app>
    <x-slot:title>Quizez Admin</x-slot:title>
    <!-- Content -->
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="table-responsive pt-0">
            <div class="d-grid px-3">
                <form id="quiz-filter-form" class="row g-3 align-items-end py-4" method="get"
                    action="{{ route('quizez.index') }}">
                    <div class="col-lg-4">
                        <label class="form-label" for="quiz-search">Search quizzes</label>
                        <input id="quiz-search" type="search" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Name or topic">
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <label class="form-label" for="quiz-difficulty">Difficulty</label>
                        <select id="quiz-difficulty" name="difficulty" class="form-select">
                            <option value="">All difficulties</option>
                            @foreach (['easy', 'medium', 'hard', 'nerd'] as $value)
                                <option value="{{ $value }}" @selected(request('difficulty') === $value)>{{ ucfirst($value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <label class="form-label" for="quiz-length">Length</label>
                        <select id="quiz-length" name="length" class="form-select">
                            <option value="">All lengths</option>
                            @foreach (['short', 'medium', 'long'] as $value)
                                <option value="{{ $value }}" @selected(request('length') === $value)>{{ ucfirst($value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <label class="form-label" for="quiz-sort">Sort</label>
                        <select id="quiz-sort" name="sort" class="form-select">
                            <option value="desc" @selected($sort === 'desc')>Newest first</option>
                            <option value="asc" @selected($sort === 'asc')>Oldest first</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-2 d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Filter</button>
                        <a class="btn btn-outline-secondary" href="{{ route('quizez.index') }}">Clear</a>
                    </div>
                </form>
                {{-- Sorting is kept in the same query-string form for fast, bookmarkable filtering. --}}
                <!--
                                <a class="dropdown-item">
                                    <div class="text-center">
                                        <form action="{{ route('quizez.index') }}">
                                            <input type="hidden" name="sort" value="{{ $sort ?? 'desc' }}" />
                                            <input type="hidden" name="search" value="{{ request('search') }}" />
                                            <input
                                                type="hidden"
                                                name="difficulty"
                                                value="{{ request('difficulty') }}"
                                            />
                                            <input type="hidden" name="length" value="{{ request('length') }}" />

                                            <button class="btn text-start" type="submit">
                                                <img
                                                    src="{{ asset($sort === 'desc' ? 'assets/images/arrow_down.svg' : 'assets/images/arrow_top.svg') }}"
                                                    alt="Arrows "
                                                />
                                            </button>
                                        </form>
                                        <hr />
                                    </div>
                                </a>
                                <a class="dropdown-item">
                                    <div class="d-flex align-items-center length-parent text-start">
                                        <div class="main-text mt-1">
                                            <span class="length">Length</span>
                                        </div>
                                        <div class="length-div">
                                            <div>
                                                <form method="get" action="{{ route('quizez.index') }}">
                                                    <input type="hidden" name="length" value="short" />
                                                    <button class="btn btn-outline-info">Short</button>
                                                </form>
                                            </div>
                                            <div>
                                                <form method="get" action="{{ route('quizez.index') }}">
                                                    <input type="hidden" name="length" value="meduim" />
                                                    <button class="btn btn-outline-info">Medium</button>
                                                </form>
                                            </div>
                                            <div>
                                                <form method="get" action="{{ route('quizez.index') }}">
                                                    <input type="hidden" name="length" value="long" />
                                                    <button class="btn btn-outline-info">long</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item">
                                    <div class="hover">
                                        <div class="d-flex justify-content-start align-items-center gap-2">
                                            <div class="main-text mt-1">
                                                <span class="length">Difficulty</span>
                                            </div>
                                            <div class="difficulty">
                                                <div>
                                                    <form method="get" action="{{ route('quizez.index') }}">
                                                        <input type="hidden" name="difficulty" value="easy" />
                                                        <button class="btn btn-outline-danger">Easy</button>
                                                    </form>
                                                </div>
                                                <div>
                                                    <form method="get" action="{{ route('quizez.index') }}">
                                                        <input type="hidden" name="difficulty" value="meduim" />
                                                        <button class="btn btn-outline-danger">Medium</button>
                                                    </form>
                                                </div>
                                                <div>
                                                    <form method="get" action="{{ route('quizez.index') }}">
                                                        <input type="hidden" name="difficulty" value="hard" />
                                                        <button class="btn btn-outline-danger">Hard</button>
                                                    </form>
                                                </div>
                                                <div>
                                                    <form method="get" action="{{ route('quizez.index') }}">
                                                        <input type="hidden" name="difficulty" value="nerd" />
                                                        <button class="btn btn-outline-danger">Nerd</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                -->
                <div class="col-12 d-flex justify-content-end">
                    <a href="{{ route('quizez.create') }}" class="btn btn-primary">
                        <i class="icon-base ti tabler-plus me-1"></i>Create quiz
                    </a>
                </div>
            </div>
            <table class="table align-middle">
                <thead>
                    <tr class="ps-3 pe-4">
                        <th>id</th>
                        <th>Name</th>
                        <th>Topic</th>
                        <th>difficulty</th>
                        <th>Length</th>
                        <th>Questions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quizez as $quiz)
                        <tr>
                            <th>{{ $quiz->id }}</th>
                            <th>{{ $quiz->name }}</th>
                            <th>{{ Str::limit($quiz->topic, 10) }}</th>
                            <th>{{ $quiz->difficulty }}</th>
                            <th>{{ $quiz->length }}</th>
                            <td>{{ $quiz->questions_count }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-sm btn-outline-info" href="{{ route('quizez.show', $quiz) }}"><i class="icon-base ti tabler-eye me-1"></i>Show</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('quizez.edit', $quiz) }}"><i class="icon-base ti tabler-pencil me-1"></i>Edit</a>
                                    <form action="{{ route('quizez.destroy', $quiz) }}" method="POST" onsubmit="return confirm('Delete this quiz?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="icon-base ti tabler-trash me-1"></i>Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="align-item-center py-4 ps-4 pe-4">{{ $quizez->appends(request()->query())->links() }}</div>
        </div>
        @if ($quizez->count() === 0)
            <h1 class="fs-5 text-center">There Is Nothing Found</h1>
        @endif
        <div class="content-backdrop fade"></div>
    </div>
</x-app>
