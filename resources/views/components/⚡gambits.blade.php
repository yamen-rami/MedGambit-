<?php

use Livewire\Component;
use Livewire\Attributes\{Computed, On};
use App\Models\Questions;
new class extends Component {
    public $search = '';
    public $length = '';
    public array $references = [];
    public $difficulty = '';
    public array $branches = [];
    public array $sp = [];
    public array $skills = [];
    public $direction;
    public $sort;
    public $count = 20;
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
        // year
        $questions = Questions::query()
            ->with(['branches', 'skills', 'specialties' , "reference" , "playedCount"])
            ->when($this->search != '', function ($query) {
                $query->whereFullText('name');
            })
            ->when($this->length != '', function ($query) {
                $query->where('length', $this->length);
            })
            ->when($this->difficulty != '', function ($query) {
                $query->where('difficulty', $this->difficulty);
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
            ->orderBy($sortColumn, $direction)
            ->simplePaginate($this->count);
        return $questions;
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

                                        <select class="form-select" wire:model.live='sort'>
                                            <option value="created_at">Created at</option>
                                            <option value="updated_at">Updated at</option>
                                            <option value="name">name</option>
                                            <option value="difficulty">Difficulty</option>
                                            <option value="length">Length</option>
                                        </select>

                                        <button type="button" class="btn btn-outline-secondary px-3">
                                            <i class="icon-base ti tabler-sort-descending"></i>
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

                                        <select class="form-select" multiple size="3">
                                            <option>Cardiology</option>
                                            <option>Neurology</option>
                                            <option>Gastroenterology</option>
                                            <option>Endocrinology</option>
                                        </select>

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Specialties
                                        </label>

                                        <select class="form-select" multiple size="3">
                                            <option>Internal Medicine</option>
                                            <option>Emergency Medicine</option>
                                            <option>Family Medicine</option>
                                            <option>Pediatrics</option>
                                        </select>

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Skills
                                        </label>

                                        <select class="form-select" multiple size="3">
                                            <option>Diagnosis</option>
                                            <option>Clinical reasoning</option>
                                            <option>Pharmacology</option>
                                            <option>Interpretation</option>
                                        </select>

                                    </div>


                                    <div>

                                        <label class="form-label fw-semibold">
                                            References
                                        </label>

                                        <select class="form-select" multiple size="3">
                                            <option>UWORLD Step 1</option>
                                            <option>UWORLD Step 2</option>
                                            <option>PassMedicine</option>
                                            <option>BMJ OnExamination</option>
                                        </select>

                                    </div>

                                </div>

                            </div>


                            {{-- Footer --}}
                            <div class="px-4 py-3 border-top bg-body-tertiary">

                                <div class="d-flex align-items-center justify-content-between">

                                    <button type="button" class="btn btn-sm btn-text-secondary">
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

                    {{-- Question 1 --}}
                    @foreach($this->questions as $question)
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
                                    {{ $question->reference }}
                                </small>
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-label-warning" @class(
                                [
                                    "badge" , 
                                    "bg-label-warning" => $
                                ]
                            )>
                                Medium
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-label-info">
                                Medium
                            </span>
                        </td>

                        <td>
                            <span class="text-muted">
                                UWorld Step 1
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end gap-2">

                                <button type="button" class="btn btn-sm btn-outline-primary">
                                    <i class="icon-base ti tabler-player-play me-1"></i>
                                    Try 
                                </button>

                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary">
                                    <i class="icon-base ti tabler-dots-vertical"></i>
                                </button>

                            </div>
                        </td>

                    </tr>
                    @endforeach


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

                </tbody>

            </table>
        </div>

        {{-- Footer --}}
        <div class="card-footer border-top">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

                <div class="text-muted small">
                    Showing
                    <strong>1</strong>
                    to
                    <strong>3</strong>
                    of
                    <strong>3</strong>
                    questions
                </div>

                <nav>
                    <ul class="pagination pagination-sm mb-0">

                        <li class="page-item disabled">
                            <a class="page-link" href="#">
                                <i class="icon-base ti tabler-chevron-left"></i>
                            </a>
                        </li>

                        <li class="page-item active">
                            <a class="page-link" href="#">
                                1
                            </a>
                        </li>

                        <li class="page-item">
                            <a class="page-link" href="#">
                                2
                            </a>
                        </li>

                        <li class="page-item">
                            <a class="page-link" href="#">
                                3
                            </a>
                        </li>

                        <li class="page-item">
                            <a class="page-link" href="#">
                                <i class="icon-base ti tabler-chevron-right"></i>
                            </a>
                        </li>

                    </ul>
                </nav>

            </div>
        </div>

    </div>
</div>
