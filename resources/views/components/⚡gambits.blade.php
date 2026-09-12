<?php

use Livewire\Component;
use Livewire\Attributes\{Computed, On};
use Livewire\WithPagination;
use App\Models\Questions;
new class extends Component {
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $quizName = '';
    public string $length = '';
    public array $references = [];
    public string $difficulty = '';
    public array $branches = [];
    public array $sp = [];
    public array $skills = [];
    public array $selectedQuestions = [];
    public string $direction = 'desc';
    public string $sort = 'created_at';
    public int $count = 20;
    #[Computed]
    public function userPlayedQuestions()
    {
        return auth()->user()->playedQuestions;
    }
    public function isPlayed($questionId)
    {
        return $this->userPlayedQuestions->contains($questionId);
    }
    #[Computed]
    public function questions()
    {
        $allowedSorts = [
            'difficulty' => 'difficulty',
            'length' => 'length',
            'name' => 'name',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];

        $direction = in_array($this->direction, ['asc', 'desc']) ? $this->direction : 'desc';
        $sortColumn = $allowedSorts[$this->sort] ?? 'created_at';

        return Questions::query()
            ->with(['branches', 'skills', 'specialties', 'reference', 'playedCount'])
            ->when($this->search !== '', function ($query) {
                $query->whereFullText(['name', 'content', 'topic'], $this->search);
            })
            ->when($this->length !== '', fn($query) => $query->where('length', $this->length))
            ->when($this->difficulty !== '', fn($query) => $query->where('difficulty', $this->difficulty))
            ->when($this->sp, fn($query) => $query->whereHas('specialties', fn($query) => $query->whereIn('specialties.id', $this->sp)))
            ->when($this->branches, fn($query) => $query->whereHas('branches', fn($query) => $query->whereIn('branch_of_medicines.id', $this->branches)))
            ->when($this->skills, fn($query) => $query->whereHas('skills', fn($query) => $query->whereIn('skills_for_questions.id', $this->skills)))
            ->when($this->references, fn($query) => $query->whereIn('reference_id', $this->references))
            ->orderBy($sortColumn, $direction)
            ->orderBy('id', $direction)
            ->paginate($this->count);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'length', 'difficulty', 'sp', 'branches', 'skills', 'references', 'sort', 'direction'], true)) {
            $this->resetPage();
        }
    }

    public function toggleDirection(): void
    {
        $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
    }

    public function clearFilters(): void
    {
        $this->reset(['length', 'difficulty', 'branches', 'sp', 'skills', 'references']);
        $this->resetPage();
        $this->dispatch('gambits-filters-cleared');
    }

    public function setLength(string $length): void
    {
        $this->length = in_array($length, ['', 'short', 'medium', 'long'], true) ? $length : '';
        $this->resetPage();
    }

    public function setDifficulty(string $difficulty): void
    {
        $this->difficulty = in_array($difficulty, ['', 'easy', 'medium', 'hard', 'nerd'], true) ? $difficulty : '';
        $this->resetPage();
    }

    public function updatedSelectedQuestions($value): void
    {
        $this->selectedQuestions = $this->normalizeIds($value);
    }

    public function toggleSelectAllVisible(): void
    {
        $visibleIds = $this->visibleQuestionIds();
        $selectedIds = $this->normalizeIds($this->selectedQuestions);

        if (count(array_intersect($visibleIds, $selectedIds)) === count($visibleIds)) {
            $this->selectedQuestions = array_values(array_diff($selectedIds, $visibleIds));
            return;
        }

        $this->selectedQuestions = array_values(array_unique([...$selectedIds, ...$visibleIds]));
    }

    public function allVisibleQuestionsSelected(): bool
    {
        $visibleIds = $this->visibleQuestionIds();

        return $visibleIds !== [] && count(array_intersect($visibleIds, $this->selectedQuestions)) === count($visibleIds);
    }

    private function visibleQuestionIds(): array
    {
        return $this->questions->pluck('id')->map(fn($id) => (int) $id)->all();
    }

    private function normalizeIds($value): array
    {
        return array_values(array_filter(array_map('intval', is_array($value) ? $value : []), fn(int $id) => $id > 0));
    }

    public function tryQuestion(int $questionId)
    {
        $question = Questions::where('id' , $questionId)->get();
        $service = new App\Services\QuizService();
        $quiz = $service->learningQuiz(questions: $question);
        return redirect()->route('start.learning.quiz', $quiz);
    }
    public $questionId;

    public function validateQuiz()
    {
        $this->validate([
            'quizName' => ['required', 'string', 'min:2', 'max:50'],
            'selectedQuestions' => ['required', 'array', 'max:20'],
        ]);
    }
    public function learningQuiz()
    {
        $this->validateQuiz();
        $questions = Questions::whereIn('id', $this->selectedQuestions)->get();
        $service = new App\Services\QuizService();
        $quiz = $service->learningQuiz(questions: $questions, name: $this->quizName);
        return redirect()->route('start.learning.quiz', $quiz);
    }
    public function examQuiz()
    {
        $this->validateQuiz();
        $questions = Questions::whereIn('id', $this->selectedQuestions)->get();
        $service = new App\Services\QuizService();
        $quiz = $service->detectedQuiz(questions: $questions, name: $this->quizName);

        return redirect()->route('start.detecated.quiz', $quiz);
    }
    public function clearSelected()
    {
        $this->selectedQuestions = [];
    }
};
?>

<div>
    @push('style')
        <style>
            .quiz-actions-grid {
                display: grid;
                grid-template-columns: 1fr;
                /* stacked by default (mobile) */
                gap: 1rem;
            }

            @media (min-width: 768px) {

                /* Bootstrap's md breakpoint */
                .quiz-actions-grid {
                    grid-template-columns: 1fr 1fr;
                }
            }

            .gambits-filter-dropdown.position-absolute {
                position: absolute !important;
                /* top: 6rem; */
                /* right: 1rem; */
                left: -53% !important;
                width: min(406px, calc(100vw - 2rem));
                max-width: calc(100vw - 2rem);
                z-index: 1050;
            }

            .gambits-filter-dropdown .gambits-select2,
            .gambits-filter-dropdown .select2-container {
                width: 100% !important;
            }

            .gambits-select2-dropdown {
                z-index: 1060;
            }

            .gambits-selection-checkbox {
                width: 1.1rem;
                height: 1.1rem;
                cursor: pointer;
            }

            .gambits-select2-wrapper {
                position: relative;
            }

            @media (max-width: 991.98px) {
                .card-footer nav {
                    width: 100%;
                    max-width: 100%;
                    overflow-x: auto;
                }

                .card-footer .pagination {
                    flex-wrap: wrap;
                }
            }

            @media (max-width: 767.98px) {
                .gambits-filter-dropdown {
                    position: fixed !important;
                    top: 4.5rem;
                    left: 0.5rem !important;
                    right: 0.5rem !important;
                    width: auto;
                    max-width: none;
                }

                .gambits-filter-dropdown .d-flex.gap-2 {
                    flex-direction: column;
                }

                .gambits-table thead {
                    display: none;
                }

                .gambits-table,
                .gambits-table tbody,
                .gambits-table tr,
                .gambits-table td {
                    display: block;
                    width: 100%;
                }

                .gambits-table tr {
                    padding: 0.75rem 1rem;
                    border-bottom: 1px solid var(--bs-border-color);
                }

                .gambits-table td {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 0.5rem 0;
                    border: 0;
                    text-align: end;
                }

                .gambits-table td::before {
                    content: attr(data-label);
                    color: var(--bs-secondary-color);
                    font-size: 0.75rem;
                    font-weight: 600;
                    text-align: start;
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                }

                .gambits-table td.gambits-question-cell {
                    display: block;
                    text-align: start;
                }

                .gambits-table td.gambits-question-cell::before {
                    display: block;
                    margin-bottom: 0.25rem;
                }

                .gambits-table td.gambits-actions-cell {
                    justify-content: flex-end;
                }

                .gambits-table td.gambits-actions-cell::before {
                    margin-right: auto;
                }

                .gambits-table .gambits-actions {
                    flex-wrap: wrap;
                    justify-content: flex-end;
                }
            }
        </style>
    @endpush

    <div class="card shadow-sm border-0">

        {{-- Header --}}
        <div class="card-header border-bottom">


            <div class="d-flex flex-column flex-xl-row justify-content-center align-items-xl-center gap-5">

                {{-- Title --}}

                {{-- Actions --}}
                <div class="d-flex align-items-center gap-2">

                    {{-- Search --}}
                    <div class="input-group input-group-merge" style="width: 280px;">
                        <span class="input-group-text">
                            <i class="icon-base ti tabler-search"></i>
                        </span>

                        <input type="text" class="form-control" placeholder="Search questions..."
                            wire:model.live.debounce.400ms="search">
                    </div>




                    <div class="position-relative" x-data="{ open: false }">

                        {{-- Filter button --}}
                        <button @click="open = !open " type="button"
                            class="btn btn-outline-secondary d-flex align-items-center gap-2">
                            <i class="icon-base ti tabler-adjustments-horizontal"></i>
                            Filters



                            <i class="icon-base ti tabler-chevron-down ms-1"></i>
                        </button>


                        {{-- Filter dropdown --}}
                        <div x-show="open" x-cloak
                            class="position-absolute end-0 mt-2 bg-card border rounded-3 shadow-lg overflow-hidden gambits-filter-dropdown">

                            {{-- Header --}}
                            <div class="px-4 py-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between">

                                    <div>
                                        <h6 class="mb-1 fw-semibold">
                                            Filter questions
                                        </h6>

                                        <small class="text-body-secondary">
                                            Refine the questions shown in the table
                                        </small>
                                    </div>

                                    <button @click="open = false " type="button"
                                        class="btn btn-sm btn-icon btn-text-secondary">
                                        <i class="icon-base ti tabler-x"></i>
                                    </button>

                                </div>
                            </div>


                            {{-- Content --}}
                            <div class="p-4" style="max-height: 65vh; overflow-y: auto;">

                                {{-- Sort --}}
                                <div class="mb-4">

                                    <label class="form-label fw-semibold mb-2">
                                        Sort
                                    </label>

                                    <div class="d-flex gap-2">

                                        <select class="form-select" wire:model.live="sort">
                                            <option value="created_at">Created at</option>
                                            <option value="updated_at">Updated at</option>
                                            <option value="name">Name</option>
                                            <option value="difficulty">Difficulty</option>
                                            <option value="length">Length</option>
                                        </select>

                                        <button type="button" wire:click="toggleDirection"
                                            class="btn btn-outline-secondary px-3" aria-label="Toggle sort direction">
                                            <i
                                                class="icon-base ti tabler-sort-{{ $direction === 'asc' ? 'ascending' : 'descending' }}"></i>
                                        </button>

                                    </div>

                                </div>


                                {{-- Difficulty --}}
                                <div class="mb-4">

                                    <label class="form-label fw-semibold mb-2">
                                        Difficulty
                                    </label>

                                    <div class="d-flex flex-wrap gap-2">

                                        <button type="button" wire:click="setDifficulty('')"
                                            class="btn btn-sm {{ $difficulty == '' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                            All
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm {{ $difficulty == 'easy' ? 'btn-success' : 'btn-outline-success' }}"
                                            wire:click="setDifficulty('easy')">
                                            Easy
                                        </button>

                                        <button type="button" wire:click="setDifficulty('medium')"
                                            class="btn btn-sm {{ $difficulty == 'medium' ? 'btn-warning' : 'btn-outline-warning' }}">
                                            Medium
                                        </button>

                                        <button type="button" wire:click="setDifficulty('hard')"
                                            class="btn btn-sm {{ $difficulty == 'hard' ? 'btn-danger' : 'btn-outline-danger' }}">
                                            Hard
                                        </button>

                                        <button type="button" wire:click="setDifficulty('nerd')"
                                            class="btn btn-sm {{ $difficulty == 'nerd' ? 'btn-dark' : 'btn-outline-dark' }}">
                                            Nerd
                                        </button>

                                    </div>

                                </div>


                                {{-- Length --}}
                                <div class="mb-4">

                                    <label class="form-label fw-semibold mb-2">
                                        Question length
                                    </label>

                                    <div class="d-flex flex-wrap gap-2">

                                        <button type="button" wire:click="setLength('')"
                                            class="btn btn-sm {{ $length == '' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                            All
                                        </button>

                                        <button type="button" wire:click="setLength('short')"
                                            class="btn btn-sm {{ $length == 'short' ? 'btn-success' : 'btn-outline-success' }}">
                                            Short
                                        </button>

                                        <button type="button" wire:click="setLength('medium')"
                                            class="btn btn-sm {{ $length == 'medium' ? 'btn-warning' : 'btn-outline-warning' }}">
                                            Medium
                                        </button>

                                        <button type="button" wire:click="setLength('long')"
                                            class="btn btn-sm {{ $length == 'long' ? 'btn-danger' : 'btn-outline-danger' }}">
                                            Long
                                        </button>

                                    </div>

                                </div>


                                {{-- Medical classification --}}
                                <div>

                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Branches
                                        </label>

                                        <div wire:ignore class="gambits-select2-wrapper">
                                            <select id="gambits-branches" class="form-select gambits-select2"
                                                multiple></select>
                                        </div>

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Specialties
                                        </label>

                                        <div wire:ignore class="gambits-select2-wrapper">
                                            <select id="gambits-specialities" class="form-select gambits-select2"
                                                multiple></select>
                                        </div>

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Skills
                                        </label>

                                        <div wire:ignore class="gambits-select2-wrapper">
                                            <select id="gambits-skills" class="form-select gambits-select2"
                                                multiple></select>
                                        </div>

                                    </div>


                                    <div>

                                        <label class="form-label fw-semibold">
                                            References
                                        </label>

                                        <div wire:ignore class="gambits-select2-wrapper">
                                            <select id="gambits-references" class="form-select gambits-select2"
                                                multiple></select>
                                        </div>

                                    </div>

                                </div>

                            </div>


                            <div class="px-4 py-3 border-top d-flex justify-content-end">
                                <button type="button" wire:click="clearFilters"
                                    class="btn btn-sm btn-outline-secondary">
                                    Clear all
                                </button>
                            </div>

                        </div>

                    </div>
                    {{-- Create Question --}}


                </div>

            </div>
        </div>


        {{-- Active Filters --}}
        @if ($difficulty || $length || count($branches) || count($sp) || count($skills) || count($references))

            <div class="px-4 py-3 border-bottom">

                <div class="d-flex align-items-center flex-wrap gap-2">

                    <div class="d-flex align-items-center gap-2 me-2">

                        <i class="icon-base ti tabler-filter text-muted"></i>

                        <span class="small fw-semibold">
                            Active filters
                        </span>

                    </div>


                    @if ($difficulty)
                        <span class="badge bg-label-warning d-flex align-items-center gap-1">
                            Difficulty: {{ ucfirst($difficulty) }}

                            <button type="button" wire:click="setDifficulty('')" class="btn btn-sm p-0 text-reset">
                                <i class="icon-base ti tabler-x"></i>
                            </button>
                        </span>
                    @endif


                    @if ($length)
                        <span class="badge bg-label-info d-flex align-items-center gap-1">
                            Length: {{ ucfirst($length) }}

                            <button type="button" wire:click="setLength('')" class="btn btn-sm p-0 text-reset">
                                <i class="icon-base ti tabler-x"></i>
                            </button>
                        </span>
                    @endif


                    @if (count($branches))
                        <span class="badge bg-label-primary">
                            {{ count($branches) }} branches
                        </span>
                    @endif


                    @if (count($sp))
                        <span class="badge bg-label-primary">
                            {{ count($sp) }} specialties
                        </span>
                    @endif


                    @if (count($skills))
                        <span class="badge bg-label-primary">
                            {{ count($skills) }} skills
                        </span>
                    @endif


                    @if (count($references))
                        <span class="badge bg-label-secondary">
                            {{ count($references) }} references
                        </span>
                    @endif

                </div>

            </div>
        @else
            <div>
                <div class="px-4 py-3 border-bottom">

                    <div class="d-flex align-items-center gap-2 text-muted">

                        <i class="icon-base ti tabler-adjustments-horizontal"></i>

                        <small>
                            No filters applied
                        </small>

                    </div>

                </div>
                <div role="alert" class="alert alert-primary m-0 p-0 py-2 rounded-0 ">
                    <div>

                        <span class="px-5">
                            <span>
                                <i class="menu-icon fa-solid fa-lightbulb"></i>
                            </span>
                            Narrow the field. Sharpen your skills.
                        </span>
                    </div>


                </div>
        @endif



        {{-- Table --}}
        <div class="table-responsive">
            @if (count($selectedQuestions))
                <div class="d-flex justify-content-between align-items-center py-2 px-4">

                    <div class="px-4 py-2  text-primary fw-semibold">
                        <span class="gambits-selected-count">{{ count($selectedQuestions) }} selected</span>
                    </div>
                    <div class="quiz-actions-grid">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"
                            class="btn btn-primary">
                            <i class="icon-base ti tabler-player-play me-1"></i>
                            Start A Quiz
                        </button>
                        <button class="btn btn-dark" wire:click="clearSelected">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                            Clear Selected
                        </button>
                    </div>

                </div>
            @endif
            <table class="table table-hover align-middle mb-0 gambits-table">

                <thead>
                    <tr>
                        <th style="width: 52px;" class="text-center">
                            <input type="checkbox" class="form-check-input gambits-selection-checkbox"
                                wire:click="toggleSelectAllVisible" @checked($this->allVisibleQuestionsSelected())
                                aria-label="Select all visible questions">
                        </th>


                        <th>
                            Question
                        </th>
                        <th>
                            Reference
                        </th>

                        <th>
                            Speciality
                        </th>
                        <th>
                            Difficulty
                        </th>

                        <th>
                            Length
                        </th>
                        <th class="text-start">Played Time</th>
                        <th class="text-start">Played </th>

                        <th class="text-start">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($this->questions as $question)
                        <tr>
                            <td class="text-center" data-label="Select">
                                <input type="checkbox" class="form-check-input gambits-selection-checkbox"
                                    wire:model.live="selectedQuestions" value="{{ $question->id }}"
                                    wire:key="selected-question-{{ $question->id }}"
                                    aria-label="Select question {{ $question->id }}">
                            </td>


                            <td class="gambits-question-cell" data-label="Question">
                                <div>
                                    <h6 class="mb-1">
                                        {{ $question->name }}
                                    </h6>

                                </div>
                            </td>
                            <td data-label="Reference">
                                <span class="text-muted">
                                    {{ $question->reference?->name }}
                                </span>
                            </td>
                            <td data-label="Reference">
                                <span class="text-muted">
                                    @forelse($question->specialties as $sp)
                                        {{ $sp->name }}
                                    @empty
                                        No Speciaility Found
                                    @endforelse
                                </span>
                            </td>
                            <td data-label="Difficulty">
                                <span
                                    class="badge {{ match ($question->difficulty) {
                                        'easy' => 'bg-label-success',
                                        'medium' => 'bg-label-warning',
                                        'hard' => 'bg-label-danger',
                                        default => 'bg-label-dark',
                                    } }}">
                                    {{ ucfirst($question->difficulty) }}
                                </span>
                            </td>

                            <td data-label="Length">
                                <span
                                    class="badge {{ match ($question->length) {
                                        'short' => 'bg-label-success',
                                        'medium' => 'bg-label-warning',
                                        'long' => 'bg-label-danger',
                                        default => 'bg-label-secondary',
                                    } }}">
                                    {{ ucfirst($question->length) }}
                                </span>
                            </td>
                            <td>
                                {{ $question->playedCount?->count ?? 0 }}
                            </td>

                            <td>
                                @if ($this->isPlayed($question->id))
                                    <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-book-open-check">
                                        <path d="M12 5v16" />
                                        <path d="m16 12 2 2 4-4" />
                                        <path
                                            d="M22 6V5a2 2 0 00-1.999-2L16 3.002A5 5 0 0012 5a5 5 0 00-4-2H4a2 2 0 00-2 2v12a2 2 0 001.999 2H8a5 5 0 014 2 5 5 0 014-2h4.001A2 2 0 0022 17v-1.344" />
                                    </svg>
                                @else
                                    <svg class="text-danger" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-book-open-x">

                                        <path d="M12 5v16" />

                                        <g transform="translate(0 -4)">
                                            <path d="m16 12 6 6" />
                                            <path d="m22 12-6 6" />
                                        </g>

                                        <path
                                            d="M22 6V5a2 2 0 00-1.999-2L16 3.002A5 5 0 0012 5a5 5 0 00-4-2H4a2 2 0 00-2 2v12a2 2 0 001.999 2H8a5 5 0 014 2 5 5 0 014-2h4.001A2 2 0 0022 17v-1.344" />
                                    </svg>
                                @endif
                            </td>

                            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static"
                                data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Quiz Info</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <x-forms.input name="quizName" wire:model="quizName"
                                                label="Quiz Name"></x-forms.input>

                                            <p>Quiz Name Will be saved on your profile </p>
                                        </div>
                                        <div class="modal-footer">
                                            <div class="d-flex justify-content-between  gap-3">
                                                <button type="button" class="btn btn-success"
                                                    wire:click='learningQuiz'>Learning Quiz</button>
                                                <button class="btn btn-warning" wire:click='examQuiz'>Exam Mode
                                                </button>
                                            </div>
                                            <div class="d-block">
                                                <p><span class="text-danger my-2">Exam Mode</span> Will affect Your Elo
                                                </p>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>


                            <td class="gambits-actions-cell" data-label="Actions">
                                <div class="d-flex justify-content-end gap-2 gambits-actions">

                                    <button type="button" 
                                        wire:click="tryQuestion({{ $question->id }})"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="icon-base ti tabler-player-play me-1"></i>
                                        Try
                                    </button>



                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No questions found.</td>
                        </tr>
                    @endforelse




                </tbody>

            </table>
        </div>

        {{-- Footer --}}
        <div class="card-footer border-top">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

                <div class="text-muted small">
                    Showing
                    <strong>{{ $this->questions->firstItem() ?? 0 }}</strong>
                    to
                    <strong>{{ $this->questions->lastItem() ?? 0 }}</strong>
                    of
                    <strong>{{ $this->questions->total() }}</strong>
                    questions
                </div>

                <nav>
                    {{ $this->questions->links() }}
                </nav>

            </div>
        </div>

    </div>
    @script
        <script>
            let syncingGambitsSelect = false;
            let gambitsSelectsInitialized = false;

            const initializeGambitsSelect = (selector, url, placeholder, property) => {
                const select = $(selector);

                if (!select.length || typeof $.fn.select2 !== 'function') {
                    return false;
                }

                if (select.data('gambits-select2-initialized') && select.hasClass('select2-hidden-accessible')) {
                    return true;
                }

                select.select2({
                    width: '100%',
                    placeholder,
                    allowClear: true,
                    dropdownCssClass: 'gambits-select2-dropdown',
                    ajax: {
                        url,
                        delay: 250,
                        data: (params) => ({
                            search: params.term || ''
                        }),
                        processResults: (data) => ({
                            results: (Array.isArray(data) ? data : []).map((item) => ({
                                id: item.id,
                                text: item.name
                            })),
                        }),
                    },
                }).off('change.gambits').on('change.gambits', function() {
                    if (!syncingGambitsSelect) {
                        $wire.set(property, ($(this).val() || []).map(Number));
                    }
                });
                select.data('gambits-select2-initialized', true);

                return true;
            };

            const initializeGambitsSelects = () => {
                if (gambitsSelectsInitialized || typeof $.fn.select2 !== 'function') {
                    return;
                }

                const initialized = [
                    initializeGambitsSelect('#gambits-branches', @js(route('getBranches')), 'Search for branches',
                        'branches'),
                    initializeGambitsSelect('#gambits-specialities', @js(route('getSpeciality')),
                        'Search for specialities', 'sp'),
                    initializeGambitsSelect('#gambits-skills', @js(route('getSkills')), 'Search for skills',
                        'skills'),
                    initializeGambitsSelect('#gambits-references', @js(route('getReferences')),
                        'Search for references', 'references'),
                ];

                gambitsSelectsInitialized = initialized.every(Boolean);
            };

            const initializeWhenReady = () => {
                initializeGambitsSelects();

                if (!gambitsSelectsInitialized) {
                    window.setTimeout(initializeWhenReady, 50);
                }
            };

            if (document.readyState === 'complete') {
                initializeWhenReady();
            } else {
                $(window).off('load.gambits').on('load.gambits', initializeWhenReady);
            }

            $wire.on('gambits-filters-cleared', () => {
                syncingGambitsSelect = true;
                $('.gambits-select2').each(function() {
                    $(this).val(null).trigger('change.select2');
                });
                syncingGambitsSelect = false;
            });
        </script>
    @endscript
</div>
