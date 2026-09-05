<?php

use App\Models\Option;
use App\Models\Questions;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $sort = 'created_at';

    public $references = [];

    public $direction = 'desc';

    public $search = '';

    public $difficulty = '';

    public $length = '';

    public $elo = '';

    public $eloIncorrect = '';

    public array $branches = [];

    public array $sp = [];

    public array $skills = [];

    #[Computed]
    public function questions()
    {
        $allowedSorts = [
            'content' => 'content',
            'topic' => 'topic',
            'difficulty' => 'difficulty',
            'reference' => 'reference',
            'length' => 'length',
        ];
        $direction = in_array($this->direction, ['asc', 'desc']) ? $this->direction : 'desc';
        $sortColumn = $allowedSorts[$this->sort] ?? 'created_at';
        $search = $this->search;
        $questions = Questions::query()
            ->with('reference')
            ->when($this->search, function ($query) {
                $query->whereFullText(['content', 'topic'], $this->search);
            })
            ->when($this->difficulty, function ($query) {
                $query->where('difficulty', $this->difficulty);
            })
            ->when($this->length, function ($query) {
                $query->where('length', $this->length);
            })
            ->when($this->sp, function ($query) {
                $query->whereHas('specialties', function ($query) {
                    $query->whereIn('specialties.id', $this->sp);
                });
            })
            ->when($this->branches, function ($query) {
                $query->whereHas('branches', function ($query) {
                    $query->whereIn('branch_of_medicines.id', $this->branches);
                });
            })
            ->when($this->skills, function ($query) {
                $query->whereHas('skills', function ($query) {
                    $query->whereIn('skills_for_questions.id', $this->skills);
                });
            })
            ->when($this->references, function ($query) {
                $query->whereHas('reference', function ($query) {
                    $query->whereIn('references.id', $this->references);
                });
            })
            ->when($this->elo, fn ($q) => $q->where('elo_correct', $this->elo))
            ->when($this->eloIncorrect, fn ($q) => $q->where('elo_incorrect', $this->eloIncorrect))
            ->orderBy($sortColumn, $direction)
            ->simplePaginate()
            ->withQueryString();

        return $questions;
    }

    public function playedTime($question) {}
};
?>

<div>
    @push('styles')
        <style>
            .select2-container--open {
                z-index: 100000 !important;
            }

            .select2-dropdown {
                z-index: 100000 !important;
            }

            #staticBackdrop .modal-body {
                overflow: visible;
            }

            #staticBackdrop .modal-content {
                overflow: visible;
            }
        </style>
    @endpush

    <div class="card">
        <div style="overflow-x: hidden" class="card-datatable table-responsive pt-0">
            <div class="d-grid">
                <div class="row align-items-center py-4">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-5 col-md-6 my-3">
                        <div class="d-flex position-relative gap-2">
                            <input
                                type="text"
                                name="search"
                                wire:model.live.debounce.300ms="search"
                                class="form-control flex-grow-1 ps-3"
                                style="min-width: 150px"
                                placeholder="Search Questions"
                            />
                            <button
                                type="button"
                                wire:show="search"
                                wire:click="$set('search', '')"
                                style="
                                    position: absolute;
                                    right: 10px;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    background: none;
                                    border: none;
                                "
                            >
                                <i class="icon-base ti tabler-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-1 text-center">
                        <div class="dropdown-center">
                            <x-filter align="start">
                                <x-slot:trigger>
                                    <span class="btn hide-arrow">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-sliders-horizontal-icon lucide-sliders-horizontal"
                                        >
                                            <path d="M10 5H3" />
                                            <path d="M12 19H3" />
                                            <path d="M14 3v4" />
                                            <path d="M16 17v4" />
                                            <path d="M21 12h-9" />
                                            <path d="M21 19h-5" />
                                            <path d="M21 5h-7" />
                                            <path d="M8 10v4" />
                                            <path d="M8 12H3" />
                                        </svg>
                                    </span>
                                </x-slot:trigger>

                                <div class="px-4 py-4" style="min-width: 220px">
                                    <div class="d-flex justify-content-end mb-2">
                                        @if ($this->direction === 'desc')
                                            <svg
                                                wire:click="$set('direction', 'asc')"
                                                wire:key="dir-asc"
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                style="cursor: pointer"
                                            >
                                                <path d="m3 8 4-4 4 4" />
                                                <path d="M7 4v16" />
                                                <path d="M11 12h4" />
                                                <path d="M11 16h7" />
                                                <path d="M11 20h10" />
                                            </svg>
                                        @else
                                            <svg
                                                wire:click="$set('direction', 'desc')"
                                                wire:key="dir-desc"
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                style="cursor: pointer"
                                            >
                                                <path d="m3 16 4 4 4-4" />
                                                <path d="M7 20V4" />
                                                <path d="M11 4h4" />
                                                <path d="M11 8h7" />
                                                <path d="M11 12h10" />
                                            </svg>
                                        @endif
                                    </div>

                                    <div class="text-start">
                                        <p
                                            wire:click="$set('sort', 'content')"
                                            wire:key="sort-name"
                                            class="mb-1 cursor-pointer py-1 pe-5"
                                            :class="$wire.sort === 'content' ? 'text-success' : 'text-body'"
                                        >
                                            Content
                                        </p>
                                        <p
                                            wire:click="$set('sort', 'topic')"
                                            wire:key="sort-name"
                                            class="mb-1 cursor-pointer py-1 pe-5"
                                            :class="$wire.sort === 'topic' ? 'text-success' : 'text-body'"
                                        >
                                            Topic
                                        </p>
                                        <p
                                            wire:click="$set('sort', 'created_at')"
                                            wire:key="sort-created"
                                            class="mb-1 cursor-pointer py-1 pe-5"
                                            :class="$wire.sort === 'created_at' ? 'text-success' : 'text-body'"
                                        >
                                            Created At
                                        </p>
                                        <p
                                            wire:click="$set('sort', 'updated_at')"
                                            wire:key="sort-updated"
                                            class="mb-0 cursor-pointer py-1 pe-5"
                                            :class="$wire.sort === 'updated_at' ? 'text-success' : 'text-body'"
                                        >
                                            Update At
                                        </p>
                                        <p
                                            type="button"
                                            class="text-body"
                                            data-bs-toggle="modal"
                                            data-bs-target="#staticBackdrop"
                                        >
                                            Advance Filtering
                                        </p>
                                    </div>
                                </div>
                            </x-filter>

                            <div
                                wire:ignore.self
                                class="modal fade"
                                id="staticBackdrop"
                                data-bs-backdrop="static"
                                data-bs-keyboard="false"
                                tabindex="-1"
                                aria-labelledby="staticBackdropLabel"
                                aria-hidden="true"
                            >
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Modal title</h1>
                                            <button
                                                type="button"
                                                class="btn-close"
                                                data-bs-dismiss="modal"
                                                aria-label="Close"
                                            ></button>
                                        </div>
                                        <div class="modal-body d-grid">
                                            <div class="d-grid">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-3">
                                                        <div class="">
                                                            <span>
                                                                <strong>Difficulty</strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <select
                                                            wire:model.live.debounce.1000ms="difficulty"
                                                            class="form-select col-lg-3"
                                                            id="difficulty"
                                                            style="max-width: 70%"
                                                        >
                                                            <option class="form-select" value="">
                                                                Select Difficulty
                                                            </option>
                                                            <option class="form-option" value="easy">Easy</option>
                                                            <option value="medium">Medium</option>
                                                            <option value="hard">Hard</option>
                                                            <option value="nerd">Nerd</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-grid my-2">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-3">
                                                        <div class="">
                                                            <span>
                                                                <strong>Length</strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <select
                                                            wire:model.live.debounce.300ms="length"
                                                            class="form-select col-lg-3"
                                                            id="difficulty"
                                                            style="max-width: 70%"
                                                        >
                                                            <option class="form-select" value="">Select Length</option>
                                                            <option value="short">Short</option>
                                                            <option value="medium">Medium</option>
                                                            <option value="long">Hard</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-grid my-1">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-3">
                                                        <div class="">
                                                            <span>
                                                                <strong>Elo Correct</strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <select
                                                            wire:model.live.debounce.300ms="elo"
                                                            class="form-select col-lg-3"
                                                            style="max-width: 70%"
                                                        >
                                                            <option class="form-select" value="">
                                                                Select Elo Correct
                                                            </option>
                                                            <option value="4">4</option>
                                                            <option value="8">8</option>
                                                            <option value="12">12</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-grid my-1">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-3">
                                                        <div class="">
                                                            <span>
                                                                <strong>Elo InCorrect </strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <select
                                                            wire:model.live.debounce.300ms="eloIncorrect"
                                                            class="form-select col-lg-3"
                                                            style="max-width: 70%"
                                                        >
                                                            <option class="form-select" value="">
                                                                Select Elo Incorrect
                                                            </option>
                                                            <option value="5">5</option>
                                                            <option value="10">10</option>
                                                            <option value="15">15</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-grid my-2">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-3">
                                                        <div class="">
                                                            <span>
                                                                <strong>References</strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-9" wire:ignore>
                                                        <select
                                                            style="z-index: 100000"
                                                            id="references"
                                                            class="form-control select2"
                                                            multiple="multiple"
                                                        ></select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-grid">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-3">
                                                        <div class="">
                                                            <span>
                                                                <strong>Branches</strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-9" wire:ignore>
                                                        <select
                                                            style="z-index: 100000"
                                                            id="branches"
                                                            class="form-control select2"
                                                            multiple="multiple"
                                                        >
                                                            <option value=""></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-grid my-2">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-3">
                                                        <div class="">
                                                            <span>
                                                                <strong>Skills</strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-9" wire:ignore>
                                                        <select
                                                            style="z-index: 100000"
                                                            id="skills"
                                                            class="form-control select2"
                                                            multiple="multiple"
                                                        ></select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-grid my-2">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-3">
                                                        <div class="">
                                                            <span>
                                                                <strong>Specialites</strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-9" wire:ignore>
                                                        <select
                                                            style="z-index: 100000"
                                                            id="specialities"
                                                            class="form-control select2"
                                                            multiple="multiple"
                                                        ></select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col text-center">
                        <a href="{{ route('questions.create') }}">
                            <button class="btn btn-primary">Create A New Question</button>
                        </a>
                    </div>
                </div>
            </div>
            <table class="datatables-basic table">
                <thead>
                    <tr class="ps-3 pe-4">
                        <th>id</th>
                        <th>Image</th>
                        <th>Content</th>
                        <th>Topic</th>
                        <th class="text-center">difficulty</th>
                        <th class="text-center">Length</th>
                        <th>Elo Correct</th>
                        <th>ELo Wrong</th>
                        <th>Reference</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->questions as $question)
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
                            <th class="text-center align-middle">
                                <span
                                    class="
            btn
            @if ($question->difficulty === 'easy') btn-outline-success
            @elseif ($question->difficulty === 'medium')
                btn-outline-warning
            @elseif ($question->difficulty === 'hard')
                btn-outline-danger
            @else
                btn-outline-dark @endif
        "
                                    style="width: 80px"
                                >
                                    {{ $question->difficulty }}
                                </span>
                            </th>

                            <th class="text-center align-middle">
                                <span
                                    style="width: 100px"
                                    class="
            @if ($question->length === 'short') btn btn-outline-success
            @elseif($question->length === 'medium') btn btn-outline-warning
            @else btn btn-outline-danger @endif
        "
                                >
                                    {{ $question->length }}
                                </span>
                            </th>
                            <th>{{ $question->elo_correct }}</th>
                            <th>{{ $question->elo_incorrect }}</th>
                            <th>{{ Str::limit($question->reference->name, 10) }}</th>
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
                                            <img src="{{ asset('assets/images/eye.svg') }}" alt="Show Questions" />
                                            Show
                                        </a>
                                        <a class="dropdown-item" href="{{ route('questions.edit', $question) }}"
                                            ><i class="icon-base ti tabler-pencil me-1"></i> Edit</a>
                                        {{-- Delete tag --}}
                                        <form action="{{ route('questions.destroy', $question->id) }}" method="POST">
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
                    @endforeach
                </tbody>
            </table>
            <div class="align-item-center py-4 ps-4 pe-4">
                {{ $this->questions->appends(request()->query())->links() }}
            </div>
        </div>
        @if ($this->questions->count() === 0)
            <h1 class="fs-5 text-center">There Is No Resutls Found</h1>
        @endif

        <div class="content-backdrop fade"></div>
    </div>
    @script
        <script>
            $(window).on('load', function () {
                $('#branches')
                    .select2({
                        placeholder: 'Search for Branches ', // Your placeholder text
                        ajax: {
                            url: "{{ route('getBranches') }}",
                            type: 'GET',
                            delay: 250,
                            data: function (params) {
                                return {
                                    search: params.term,
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.map((branch) => ({
                                        id: branch.id,
                                        text: branch.name,
                                    })),
                                };
                            },
                        },
                    })
                    .on('change', function () {
                        $wire.set('branches', $(this).val());
                    });
                $('#skills')
                    .select2({
                        placeholder: 'Search for Branches ', // Your placeholder text
                        ajax: {
                            url: "{{ route('getSkills') }}",
                            type: 'GET',
                            delay: 250,
                            data: function (params) {
                                return {
                                    search: params.term,
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.map((skill) => ({
                                        id: skill.id,
                                        text: skill.name,
                                    })),
                                };
                            },
                        },
                    })
                    .on('change', function () {
                        $wire.set('skills', $(this).val());
                    });
                $('#specialities')
                    .select2({
                        placeholder: 'Search for Branches ', // Your placeholder text
                        ajax: {
                            url: "{{ route('getSpeciality') }}",
                            type: 'GET',
                            delay: 250,
                            data: function (params) {
                                return {
                                    search: params.term,
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.map((sp) => ({
                                        id: sp.id,
                                        text: sp.name,
                                    })),
                                };
                            },
                        },
                    })
                    .on('change', function () {
                        $wire.set('sp', $(this).val());
                    });
                $('#references')
                    .select2({
                        placeholder: 'Search for Branches ', // Your placeholder text
                        ajax: {
                            url: "{{ route('getReferences') }}",
                            type: 'GET',
                            delay: 250,
                            data: function (params) {
                                return {
                                    search: params.term,
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.map((ref) => ({
                                        id: ref.id,
                                        text: ref.name,
                                    })),
                                };
                            },
                        },
                    })
                    .on('change', function () {
                        $wire.set('references', $(this).val());
                    });
            });
        </script>
    @endscript
</div>
