<x-app>
  <x-slot:title>
    Quizez Admin
  </x-slot:title>
  <!-- Content -->
  <!-- DataTable with Buttons -->
  <div class="card">
    <div style=" overflow-x: hidden;" class="card-datatable table-responsive pt-0">
      <div class="d-grid">
        <div class="row py-4   align-items-center">
          <div class="col-lg-4 text-center">
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow " data-bs-toggle="dropdown">
                <div class="">
                  <img src="{{ asset("assets/images/filter.svg") }}" alt="">
                </div>
              </button>
              <div class="dropdown-menu ">
                <a class="dropdown-item">
                  <div class="text-center">
                    <form action="{{ route("quizez.index") }}">
                      <input type="hidden" name="sort" value="{{ $sort ?? "desc" }}">
                      <input type="hidden" name="search" value="{{ request('search') }}">
                      <input type="hidden" name="difficulty" value="{{ request('difficulty') }}">
                      <input type="hidden" name="length" value="{{ request('length') }}">

                      <button class="btn text-start" type="submit">
                        <img
                          src="{{ asset($sort === "desc" ? "assets/images/arrow_down.svg" : "assets/images/arrow_top.svg") }}"
                          alt="Arrows ">
                      </button>
                    </form>
                    <hr>

                  </div>
                </a>
                <a class="dropdown-item">
                  <div class="d-flex text-start align-items-center length-parent">
                    <div class="mt-1 main-text ">
                      <span class="length">Length</span>
                    </div>
                    <div class="length-div">
                      <div>
                        <form method="get" action="{{ route('quizez.index') }}">
                          <input type="hidden" name="length" value="short">
                          <button class="btn btn-outline-info ">Short</button>
                        </form>
                      </div>
                      <div>
                        <form method="get" action="{{ route('quizez.index') }}">
                          <input type="hidden" name="length" value="meduim">
                          <button class="btn btn-outline-info ">Medium</button>
                        </form>
                      </div>
                      <div>
                        <form method="get" action="{{ route('quizez.index') }}">
                          <input type="hidden" name="length" value="long">
                          <button class="btn btn-outline-info">long</button>
                        </form>
                      </div>
                    </div>

                  </div>
                </a>
                <a class="dropdown-item">
                  <div class="hover">
                    <div class="d-flex justify-content-start gap-2 align-items-center">
                      <div class="mt-1 main-text ">
                        <span class="length">Difficulty</span>
                      </div>
                      <div class="difficulty">
                        <div>
                          <form method="get" action="{{ route('quizez.index') }}">
                            <input type="hidden" name="difficulty" value="easy">
                            <button class="btn btn-outline-danger">Easy</button>
                          </form>
                        </div>
                        <div>
                          <form method="get" action="{{ route('quizez.index') }}">
                            <input type="hidden" name="difficulty" value="meduim">
                            <button class="btn btn-outline-danger">Medium</button>
                          </form>
                        </div>
                        <div>
                          <form method="get" action="{{ route('quizez.index') }}">
                            <input type="hidden" name="difficulty" value="hard">
                            <button class="btn btn-outline-danger">Hard</button>
                          </form>
                        </div>
                        <div>
                          <form method="get" action="{{ route('quizez.index') }}">
                            <input type="hidden" name="difficulty" value="nerd">
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
          <div class="col-lg-4 col-md-4 ">
            <form class="form" action="{{ route("quizez.index") }}">
              <div class="d-flex gap-3">
                <input type="text" name="search" class="form-control ps-5" placeholder="Search quizez">
                <button class="btn btn-danger "><a class="text-white"
                    href="{{ route("quizez.index") }}">Clear</a></button>
              </div>
            </form>
          </div>
          <div class="col-lg-4 text-center">
            <a href="{{ route("quizez.create") }}">
              <button class="btn btn-primary">
                Create A New Quiz
              </button>
            </a>
          </div>
        </div>
      </div>
      <table class="datatables-basic table ">

        <thead>
          <tr class="ps-3 pe-4">
            <th>id</th>
            <th>Name</th>
            <th>Topic</th>
            <th>difficulty</th>
            <th>Length</th>

            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($quizez as $quiz)
            <tr>
              <th>{{ $quiz->id}}</th>
              <th>{{ $quiz->name}}</th>
              <th>{{ Str::limit($quiz->topic, 10)}}</th>
              <th>{{ $quiz->difficulty}}</th>
              <th>{{ $quiz->length}}</th>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="icon-base ti tabler-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu">
                    {{-- Edit tag --}}
                    <a class="dropdown-item" href="{{ route('quizez.show', $quiz) }}">
                      <img src="{{ asset("assets/images/eye.svg") }}" alt="Show quizez">
                      Show
                    </a>
                    <a class="dropdown-item" href="{{ route('quizez.edit', $quiz) }}"><i
                        class="icon-base ti tabler-pencil me-1"></i>
                      Edit</a>
                    {{-- Delete tag --}}
                    <form action="{{ route("quizez.destroy", $quiz->id) }}" method="POST">
                      @csrf
                      @method("DELETE")
                      <button type="submit" class="dropdown-item"><i class="icon-base ti tabler-trash me-1"></i>
                        Delete</button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div class="ps-4 pe-4 py-4 align-item-center">
        {{ $quizez->appends(request()->query())->links() }}
      </div>
    </div>
    @if($quizez->count() === 0)
      <h1 class="text-center fs-5">There Is Nothing Found</h1>
    @endif
    <div class="content-backdrop fade"></div>
  </div>
</x-app>