<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Questions;
new class extends Component {
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $length = '';
    public array $references = [];
    public string $difficulty = '';
    public array $branches = [];
    public array $sp = [];
    public array $skills = [];
    public string $direction = 'desc';
    public string $sort = 'created_at';
    public int $count = 20;

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
            ->with(['branches', 'skills', 'specialties', 'reference'])
            ->when($this->search !== '', function ($query) {
                $query->whereFullText(['name', 'content', 'topic'], $this->search);
            })
            ->when($this->length !== '', fn ($query) => $query->where('length', $this->length))
            ->when($this->difficulty !== '', fn ($query) => $query->where('difficulty', $this->difficulty))
            ->when($this->sp, fn ($query) => $query->whereHas(
                'specialties',
                fn ($query) => $query->whereIn('specialties.id', $this->sp)
            ))
            ->when($this->branches, fn ($query) => $query->whereHas(
                'branches',
                fn ($query) => $query->whereIn('branch_of_medicines.id', $this->branches)
            ))
            ->when($this->skills, fn ($query) => $query->whereHas(
                'skills',
                fn ($query) => $query->whereIn('skills_for_questions.id', $this->skills)
            ))
            ->when($this->references, fn ($query) => $query->whereIn('reference_id', $this->references))
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

    public function tryQuestion(int $questionId): void
    {
        dd("here");
    }
};
?>

<div>
    <div class="card shadow-sm border-0">

        {{-- Header --}}
        <div class="card-header border-bottom">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-5">

                {{-- Title --}}
                <div>
                    <h5 class="mb-1">Questions</h5>

                    <p class="text-muted mb-0">
                        Manage and review your medical questions.
                    </p>
                </div>

                @script
                <script>
                    const initializeGambitsSelect = (selector, url, placeholder, property) => {
                        const select = $(selector);

                        if (!select.length) {
                            return;
                        }

                        if (select.hasClass('select2-hidden-accessible')) {
                            select.select2('destroy');
                        }

                        select.select2({
                            width: '100%',
                            placeholder,
                            ajax: {
                                url,
                                delay: 250,
                                data: (params) => ({ search: params.term }),
                                processResults: (data) => ({
                                    results: data.map((item) => ({ id: item.id, text: item.name })),
                                }),
                            },
                        }).off('change.gambits').on('change.gambits', function () {
                            $wire.set(property, $(this).val() ?? []);
                        });
                    };

                    initializeGambitsSelect('#gambits-branches', @js(route('getBranches')), 'Search for branches', 'branches');
                    initializeGambitsSelect('#gambits-specialities', @js(route('getSpeciality')), 'Search for specialities', 'sp');
                    initializeGambitsSelect('#gambits-skills', @js(route('getSkills')), 'Search for skills', 'skills');
                    initializeGambitsSelect('#gambits-references', @js(route('getReferences')), 'Search for references', 'references');

                    $wire.on('gambits-filters-cleared', () => {
                        $('.gambits-select2').val(null).trigger('change');
                    });
                </script>
                @endscript

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
                        <div x-show="open"
                            class="position-absolute end-0 mt-2 bg-card border rounded-3 shadow-lg overflow-hidden"
                            style="
            width: 760px;
            max-width: calc(100vw - 32px);
            z-index: 1050;
        ">

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
                                            <i class="icon-base ti tabler-sort-{{ $direction === 'asc' ? 'ascending' : 'descending' }}"></i>
                                        </button>

                                    </div>

                                </div>


                                {{-- Difficulty --}}
                                <div class="mb-4">

                                    <label class="form-label fw-semibold mb-2">
                                        Difficulty
                                    </label>

                                    <div class="d-flex flex-wrap gap-2">

                                        <button type="button" wire:click="$set('difficulty', '')"
                                            class="btn btn-sm {{ $difficulty == '' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                            All
                                        </button>

                                        <button
                                            class="btn btn-sm {{ $difficulty == 'easy' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                            wire:click="$set('difficulty' , 'easy')">
                                            Easy
                                        </button>

                                        <button wire:click="$set('difficulty' , 'medium')"
                                            class="btn btn-sm {{ $difficulty == 'medium' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                            Medium
                                        </button>

                                        <button wire:click="$set('difficulty' , 'hard')"
                                            class="btn btn-sm {{ $difficulty == 'hard' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                            Hard
                                        </button>

                                        <button wire:click="$set('difficulty' , 'nerd')"
                                            class="btn btn-sm {{ $difficulty == 'nerd' ? 'btn-primary' : 'btn-outline-secondary' }}">
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

                                        <button wire:click="$set('length' , '')"
                                            class="btn btn-sm {{ $length == '' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                            All
                                        </button>

                                        <button wire:click="$set('length' , 'short')"
                                            class="btn btn-sm {{ $length == 'short' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            Short
                                        </button>

                                        <button wire:click="$set('length', 'medium')"
                                            class="btn btn-sm {{ $length == 'medium' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                            Medium
                                        </button>

                                        <button wire:click="$set('length' , 'long')"
                                            class="btn btn-sm {{ $length == 'long' ? 'btn-primary' : 'btn-outline-secondary' }}">
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

                                        <select id="gambits-branches" class="form-select gambits-select2" multiple
                                            wire:ignore>
                                        </select>

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Specialties
                                        </label>

                                        <select id="gambits-specialities" class="form-select gambits-select2" multiple
                                            wire:ignore>
                                        </select>

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Skills
                                        </label>

                                        <select id="gambits-skills" class="form-select gambits-select2" multiple
                                            wire:ignore>
                                        </select>

                                    </div>


                                    <div>

                                        <label class="form-label fw-semibold">
                                            References
                                        </label>

                                        <select id="gambits-references" class="form-select gambits-select2" multiple
                                            wire:ignore>
                                        </select>

                                    </div>

                                </div>

                            </div>


                            {{-- Footer --}}
                            <div class="px-4 py-3 border-top bg-body-tertiary">

                                <div class="d-flex align-items-center justify-content-between">

                                    <button type="button" wire:click="clearFilters" class="btn btn-sm btn-text-secondary">
                                        Clear all
                                    </button>

                                    <button type="button" class="btn btn-sm btn-primary">
                                        Done
                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>
                    {{-- Create Question --}}
                    <button type="button" class="btn btn-primary">
                        <i class="icon-base ti tabler-plus me-1"></i>
                        Create Question
                    </button>

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

                            <button type="button" wire:click="$set('difficulty', '')"
                                class="btn btn-sm p-0 text-reset">
                                <i class="icon-base ti tabler-x"></i>
                            </button>
                        </span>
                    @endif


                    @if ($length)
                        <span class="badge bg-label-info d-flex align-items-center gap-1">
                            Length: {{ ucfirst($length) }}

                            <button type="button" wire:click="$set('length', '')" class="btn btn-sm p-0 text-reset">
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
            <div class="px-4 py-3 border-bottom">

                <div class="d-flex align-items-center gap-2 text-muted">

                    <i class="icon-base ti tabler-adjustments-horizontal"></i>

                    <small>
                        No filters applied
                    </small>

                </div>

            </div>

        @endif



        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>

                        <th>
                            Question
                        </th>

                        <th>
                            Difficulty
                        </th>

                        <th>
                            Length
                        </th>

                        <th>
                            Reference
                        </th>

                        <th class="text-start">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($this->questions as $question)
                    <tr>

                        <td>
                            <span class="text-muted">
                                #{{ $question->id }}
                            </span>
                        </td>

                        <td>
                            <div>
                                <h6 class="mb-1">
                                   {{ $question->name }}
                                </h6>

                                <small class="text-muted">
                                    {{ $question->reference?->name }}
                                </small>
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-label-warning">
                                {{ ucfirst($question->difficulty) }}
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-label-info">
                                {{ ucfirst($question->length) }}
                            </span>
                        </td>

                        <td>
                            <span class="text-muted">
                                {{ $question->reference?->name }}
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end gap-2">

                                <button type="button" wire:click="tryQuestion({{ $question->id }})"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="icon-base ti tabler-player-play me-1"></i>
                                    Try 
                                </button>

                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary">
                                    <i class="icon-base ti tabler-dots-vertical"></i>
                                </button>

                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No questions found.</td>
                    </tr>
                    @endforelse


                    @if (false)
                    {{-- Question 2 --}}
                    <tr>

                        <td>
                            <span class="text-muted">
                                #1025
                            </span>
                        </td>

                        <td>
                            <div>
                                <h6 class="mb-1">
                                    Which receptor is primarily responsible for the effects of adrenaline?
                                </h6>

                                <small class="text-muted">
                                    Pharmacology
                                </small>
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-label-success">
                                Easy
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-label-success">
                                Short
                            </span>
                        </td>

                        <td>
                            <span class="text-muted">
                                PassMedicine
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end gap-2">

                                <button type="button" class="btn btn-sm btn-outline-primary">
                                    <i class="icon-base ti tabler-player-play me-1"></i>
                                    Try Question
                                </button>

                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary">
                                    <i class="icon-base ti tabler-dots-vertical"></i>
                                </button>

                            </div>
                        </td>

                    </tr>

                    {{-- Question 3 --}}
                    <tr>

                        <td>
                            <span class="text-muted">
                                #1026
                            </span>
                        </td>

                        <td>
                            <div>
                                <h6 class="mb-1">
                                    What is the mechanism of action of ACE inhibitors?
                                </h6>

                                <small class="text-muted">
                                    Pharmacology
                                </small>
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-label-danger">
                                Hard
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-label-primary">
                                Long
                            </span>
                        </td>

                        <td>
                            <span class="text-muted">
                                MRCP PasTest
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end gap-2">

                                <button type="button" class="btn btn-sm btn-outline-primary">
                                    <i class="icon-base ti tabler-player-play me-1"></i>
                                    Try Question
                                </button>

                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary">
                                    <i class="icon-base ti tabler-dots-vertical"></i>
                                </button>

                            </div>
                        </td>

                    </tr>
                    @endif

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
</div>
