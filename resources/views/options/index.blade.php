<x-app>
    <x-slot:title>Questions Admin</x-slot:title>
    <!-- Content -->
    <!-- DataTable with Buttons -->
    <div class="card">
        <div style="overflow: hidden" class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <div class="d-grid">
                    <div class="row align-items-center py-4">
                        <div class="col-lg-4 text-center">
                            <div class="dropdown">
                                <button
                                    type="button"
                                    class="btn dropdown-toggle hide-arrow p-0"
                                    data-bs-toggle="dropdown"
                                >
                                    <div class="">
                                        <img src="{{ asset("assets/images/filter.svg") }}" alt="" />
                                    </div>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item">
                                        <div>
                                            <form action="{{ route("questions.index") }}">
                                                <input type="hidden" name="sort" value="{{ $sort ?? "desc" }}" />
                                                <input type="hidden" name="search" value="{{ request('search') }}" />
                                                <button class="btn" type="submit">
                                                    <img
                                                        src="{{ asset($sort === "desc" ? "assets/images/arrow_down.svg" : "assets/images/arrow_top.svg") }}"
                                                        alt=""
                                                    />
                                                </button>
                                            </form>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <form class="form" action="{{ route("questions.index") }}">
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control ps-5"
                                    placeholder="Search Questions"
                                />
                            </form>
                        </div>
                        <div class="col-lg-4 text-center">
                            <a href="{{ route("questions.create") }}">
                                <button class="btn btn-primary">Create A New Question</button>
                            </a>
                        </div>
                    </div>
                </div>
                <thead>
                    <tr class="ps-3 pe-4">
                        <th>id</th>
                        <th>Image</th>
                        <th>Content</th>
                        <th>Topic</th>
                        <th>Solved</th>
                        <th>difficulty</th>
                        <th>Length</th>
                        <th>Correct</th>
                        <th>Incorrect</th>
                        <th>Reference</th>
                        <th>Actions</th>
                    </tr>
                    o
                </thead>
                <tbody>
                    @forelse ($questions as $question)
                    <tr>
                        <th>{{ $question->id }}</th>
                        <td>
                            <ul class="list-unstyled avatar-group d-flex align-items-center m-0">
                                <li
                                    data-bs-toggle="tooltip"
                                    data-popup="tooltip-custom"
                                    data-bs-placement="top"
                                    class="avatar avatar-xs pull-up"
                                    title="{{ $question->content }}"
                                >
                                    <img src="{{ asset($question->image) }}" alt="Avatar" class="rounded-circle" />
                                </li>
                            </ul>
                        </td>
                        <th>{{ Str::limit($question->content, 10) }}</th>
                        <th>{{ Str::limit($question->topic, 10) }}</th>
                        <th>{{ $question->solved }}</th>
                        <th>{{ $question->difficulty }}</th>

                        <th>{{ $question->length }}</th>
                        <th>{{ $question->elo_correct }}</th>
                        <th>{{ $question->elo_incorrect }}</th>
                        <th>{{ Str::limit($question->reference, 10) }}</th>
                        <td>
                            <div class="dropdown">
                                <button
                                    type="button"
                                    class="btn dropdown-toggle hide-arrow p-0"
                                    data-bs-toggle="dropdown"
                                >
                                    <i class="icon-base ti tabler-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    {{-- Edit tag --}}
                                    <a class="dropdown-item" href="{{ route('questions.show', $question) }}">
                                        <img src="{{ asset("assets/images/eye.svg") }}" alt="Show Questions" />
                                        Show
                                    </a>
                                    <a class="dropdown-item" href="{{ route('questions.edit', $question) }}"
                                        ><i class="icon-base ti tabler-pencil me-1"></i> Edit</a>
                                    {{-- Delete tag --}}
                                    <form action="{{ route("questions.destroy", $question->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item">
                                            <i class="icon-base ti tabler-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                @empty
                @endforelse
            </table>
            <div class="align-item-center py-4 ps-4 pe-4">{{ $options->appends(request()->query())->links() }}</div>
        </div>

        <div class="content-backdrop fade"></div>
    </div>
</x-app>
