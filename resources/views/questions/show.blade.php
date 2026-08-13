<x-app>
    <x-slot:title>Showing {{ $question->topic }}</x-slot:title>
    <div class="card my-2 py-2">
        <div class="container pt-2">
            <div class="text-start">
                <p><strong>Question Content : </strong></p>
                <p><span>{{ $question->content }}</span></p>
                <div class="my-2 text-center">
                    <img width="70%" height="400px" class="rounded-5" src="{{ asset($question->image) }}" alt="" />
                </div>
                <hr />
                {{-- --}}
                {{-- T --}}
                @foreach ($question->options as $option)
                    <div
                        style="background-color: rgba(164, 164, 196, 0.342)"
                        class="option d-flex align-items-center  py-2 rounded-5 my-2 ps-5 {{ $question->correctAnswer->id == $option->id ? 'border border-success border-1 text-success option-correct' : 'border border-secondary border-1 text-white' }}"
                    >
                        <h6 class="fw-bold fs-5 my-2">{{ $option->name }}</h6>
                        <p class="form-label fw-bold fs-5 my-2 ps-4 pe-4">{{ $option->content }}</p>
                    </div>
                @endforeach
                <hr />
                <p><strong>Question Topic</strong></p>
                <p><span>{{ $question->topic }}</span></p>
                <hr />

                <p><strong>Question Main Explanation</strong></p>
                <p><span>{{ $question->main_explanation }}</span></p>
                <hr />

                <p><strong>Question High Yield</strong></p>
                <p><span>{{ $question->high_yield }}</span></p>
                <hr />

                <p><strong>Question Speciality</strong></p>
                <div class="d-flex align-items-center gap-2 px-2">
                    @foreach ($question->specialties as $speciality)
                        <button class="btn btn-outline-primary">{{ $speciality->name }}</button>
                    @endforeach
                </div>
                <hr />
                <p><strong>Question Skills</strong></p>
                <div class="d-flex align-items-center gap-2 px-2">
                    @foreach ($question->skills as $skill)
                        <button class="btn btn-outline-primary">{{ $skill->name }}</button>
                    @endforeach
                </div>
                <hr />
                <p><strong>Question Branch For Medicine</strong></p>
                <div class="d-flex align-items-center gap-2 px-2">
                    @foreach ($question->branches as $branch)
                        <button class="btn btn-outline-primary">{{ $branch->name }}</button>
                    @endforeach
                </div>
                <hr />
            </div>
        </div>
    </div>
    <div class="d-grid card parent my-1 py-5" id="parent">
        <div class="">
            <div class="container">
                <div class="row align-items-center justify-around">
                    @if ($question->options->count() < 5)
                        <div class="col-lg-12 text-end">
                            <button class="btn btn-info">
                                <a href="{{ route("option.create", $question->id) }}">
                                    <span class="text-white">Add A New Option</span>
                                </a>
                            </button>
                        </div>
                    @endif

                    @forelse ($question->options as $option)
                    <div class="col-lg-12">
                        <div class="popout" id="popout-{{ $option->id }}">
                            <div>
                                <div>
                                    <img class="optionImage" src="{{ asset($option->image) }}" alt="Option " />
                                </div>
                            </div>
                            <button class="btn optionButton" onclick="closeButton({{ $option->id }})">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    height="24px"
                                    viewBox="0 -960 960 960"
                                    width="24px"
                                    fill="#8B1A10"
                                >
                                    <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                                </svg>
                            </button>
                        </div>
                        {{-- Where are the change --}}
                    </div>
                    <div class="col-lg-6 col-sm-6 col-sm-12 my-3">
                        <div class="my-1">
                            <div>
                                <span>Option :{{ $option->name }}</span>
                            </div>
                        </div>
                        <div class="my-1">
                            <span>{{ $option->content }}</span>
                        </div>
                        <hr />
                        <p>{{ $option->explanation }}</p>
                    </div>
                    <div class="col-lg-6 d-flex align-items-center justify-content-end gap-3">
                        <div>
                            <button class="btn btn-info" onclick="popout({{ $option->id }})">
                                See Mentioned Image
                            </button>
                        </div>
                        <div class="my-1">
                            <div>
                                <div class="my-2">
                                    <a
                                        class="btn btn-outline-primary d-inline-block pl-4"
                                        href="{{ route('options.edit', $option->id) }}"
                                    ><strong>Edit Option ...</strong></a>
                                </div>
                            </div>
                        </div>
                        <div class="my-1">
                            <div>
                                <div class="my-1">
                                    <form
                                        method="post"
                                        action="{{ route("option.destroy", ['optionId' => $option->id, 'questionId' => $question->id]) }}"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <!-- Button trigger modal -->
                                        <button
                                            type="button"
                                            class="btn btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#exampleModal"
                                        >
                                            Delete
                                        </button>
                                        <!-- Modal -->
                                        <div
                                            class="modal fade"
                                            id="exampleModal"
                                            tabindex="-1"
                                            aria-labelledby="exampleModalLabel"
                                            aria-hidden="true"
                                        >
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel">
                                                            Are You Sure You Want Delete Option : {{ $option->name }}
                                                        </h1>
                                                        <button
                                                            type="button"
                                                            class="btn-close"
                                                            data-bs-dismiss="modal"
                                                            aria-label="Close"
                                                        ></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="fw-bold">{{ $option->content }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button
                                                            type="button"
                                                            class="btn btn-secondary"
                                                            data-bs-dismiss="modal"
                                                        >
                                                            Close
                                                        </button>
                                                        <button type="submit" class="btn btn-danger">
                                                            Delete Option
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                </div>
                <h1>There is No Option Found</h1>
                @endforelse
                <hr />
            </div>
        </div>
    </div>
    @foreach ($question->options as $option)
        <div class="popout" id="popout-{{ $option->id }}">
            <div>
                <div>
                    <img class="optionImage" src="{{ asset($option->image) }}" alt="Option " />
                </div>
            </div>
            <button class="btn optionButton" onclick="closeButton({{ $option->id }})">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#8B1A10">
                    <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                </svg>
            </button>
        </div>
        <div class="overlay" onclick="closeOverlay({{ $option->id }})"></div>
    @endforeach
    <hr />
    @push('scripts')
        <script src="{{ asset('assets/js/show.js') }}"></script>

    @endpush
</x-app>
