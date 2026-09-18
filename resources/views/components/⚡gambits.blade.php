<?php

use Livewire\Component;
use Livewire\Attributes\{Computed, On, Url};
use Livewire\WithPagination;
use App\Models\Questions;
use App\Models\BranchOfMedicine;
use App\Models\Reference;
use App\Models\SkillsForQuestion;
use App\Models\Specialty;

new class extends Component {
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';
    #[Url]
    public string $search = '';
    public string $quizName = '';
    public bool $showQuizModal = false;
    #[Url]
    public string $length = '';
    public array $references = [];
    #[Url]
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
                $search = '%' . $this->search . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)->orWhere('content', 'like', $search)->orWhere('topic', 'like', $search);
                });
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

    #[Computed]
    public function difficultyCounts()
    {
        return Questions::query()->selectRaw('difficulty, count(*) as total')->groupBy('difficulty')->pluck('total', 'difficulty');
    }

    #[Computed]
    public function lengthCounts()
    {
        return Questions::query()->selectRaw('length, count(*) as total')->groupBy('length')->pluck('total', 'length');
    }

    #[Computed]
    public function activeSpecialties()
    {
        return Specialty::query()
            ->whereKey($this->normalizeIds($this->sp))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function activeReferences()
    {
        return Reference::query()
            ->whereKey($this->normalizeIds($this->references))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function activeBranches()
    {
        return BranchOfMedicine::query()
            ->whereKey($this->normalizeIds($this->branches))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function activeSkills()
    {
        return SkillsForQuestion::query()
            ->whereKey($this->normalizeIds($this->skills))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function activeFilterCount(): int
    {
        return (int) ($this->difficulty !== '') + (int) ($this->length !== '') + (int) ($this->search !== '') + count($this->sp) + count($this->references) + count($this->branches) + count($this->skills);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'length', 'difficulty', 'sp', 'branches', 'skills', 'references', 'sort', 'direction', 'count'], true)) {
            $this->resetPage();
            $this->dispatch('gambits-select2-sync', branches: $this->branches, specialties: $this->sp, skills: $this->skills, references: $this->references);
        }
    }

    public function updatedCount($value): void
    {
        $this->count = in_array((int) $value, [10, 20, 50], true) ? (int) $value : 20;
        $this->resetPage();
    }

    public function toggleDirection(): void
    {
        $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'length', 'difficulty', 'branches', 'sp', 'skills', 'references']);
        $this->resetPage();
        $this->dispatch('gambits-filters-cleared');
        $this->dispatch('gambits-select2-sync', branches: [], specialties: [], skills: [], references: []);
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

    public function updatedSp($value): void
    {
        $this->sp = $this->normalizeIds($value);
        $this->resetPage();
    }

    public function updatedReferences($value): void
    {
        $this->references = $this->normalizeIds($value);
        $this->resetPage();
    }

    public function updatedBranches($value): void
    {
        $this->branches = $this->normalizeIds($value);
        $this->resetPage();
    }

    public function updatedSkills($value): void
    {
        $this->skills = $this->normalizeIds($value);
        $this->resetPage();
    }

    public function removeSpecialty(int $specialtyId): void
    {
        $this->sp = array_values(array_diff($this->normalizeIds($this->sp), [$specialtyId]));
        $this->resetPage();
        $this->dispatch('gambits-select2-sync', branches: $this->branches, specialties: $this->sp, skills: $this->skills, references: $this->references);
    }

    public function removeReference(int $referenceId): void
    {
        $this->references = array_values(array_diff($this->normalizeIds($this->references), [$referenceId]));
        $this->resetPage();
        $this->dispatch('gambits-select2-sync', branches: $this->branches, specialties: $this->sp, skills: $this->skills, references: $this->references);
    }

    public function removeBranch(int $branchId): void
    {
        $this->branches = array_values(array_diff($this->normalizeIds($this->branches), [$branchId]));
        $this->resetPage();
        $this->dispatch('gambits-select2-sync', branches: $this->branches, specialties: $this->sp, skills: $this->skills, references: $this->references);
    }

    public function removeSkill(int $skillId): void
    {
        $this->skills = array_values(array_diff($this->normalizeIds($this->skills), [$skillId]));
        $this->resetPage();
        $this->dispatch('gambits-select2-sync', branches: $this->branches, specialties: $this->sp, skills: $this->skills, references: $this->references);
    }

    public function toggleQuestion(int $questionId): void
    {
        if (!Questions::whereKey($questionId)->exists()) {
            return;
        }

        $selectedIds = $this->normalizeIds($this->selectedQuestions);

        if (in_array($questionId, $selectedIds, true)) {
            $this->selectedQuestions = array_values(array_diff($selectedIds, [$questionId]));
            return;
        }
        $this->selectedQuestions = [...$selectedIds, $questionId];
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
        $question = Questions::where('id', $questionId)->get();
        $service = new App\Services\QuizService();
        $quiz = $service->learningQuiz(questions: $question);
        return redirect()->route('start.learning.quiz', $quiz);
    }

    public function openQuizModal(): void
    {
        if ($this->selectedQuestions === []) {
            $this->addError('selectedQuestions', 'Select at least one question to start a quiz.');
            return;
        }

        $this->resetErrorBag();
        $this->showQuizModal = true;
    }

    public function closeQuizModal(): void
    {
        $this->showQuizModal = false;
        $this->resetValidation();
    }
    public $questionId;

    public function validateQuiz()
    {
        $this->validate([
            'quizName' => ['nullable', 'string', 'max:50'],
            'selectedQuestions' => ['required', 'array', 'min:1', 'max:20'],
            'selectedQuestions.*' => ['integer', 'exists:questions,id'],
        ]);
    }
    public function learningQuiz()
    {
        $this->validateQuiz();
        $questions = Questions::whereIn('id', $this->selectedQuestions)->get();
        $service = new App\Services\QuizService();
        $quiz = $service->learningQuiz(questions: $questions, name: filled($this->quizName) ? $this->quizName : null, count: $questions->count());
        return redirect()->route('start.learning.quiz', $quiz);
    }
    public function examQuiz()
    {
        $this->validateQuiz();
        $questions = Questions::whereIn('id', $this->selectedQuestions)->get();
        $service = new App\Services\QuizService();
        $quiz = $service->detectedQuiz(questions: $questions, name: filled($this->quizName) ? $this->quizName : null, count: $questions->count());

        return redirect()->route('start.detecated.quiz', $quiz);
    }
    public function clearSelected()
    {
        $this->selectedQuestions = [];
    }
};
?>

<div class="gambit-page" x-data="{ quizModalOpen: false }" @keydown.escape.window="quizModalOpen = false">
    <main class="gambit-main py-0">
        <div class="gambit-container">
            <section class="gambit-header">

                <div class="row align-items-end g-4 mt-1">
                    <div class="col">
                        <h1>Gambits</h1>
                        <p class="my-2">Shape your practice. Find your gambit.</p>
                    </div>
                    <div class="col-lg-7 d-none">
                        <div class="gambit-search"><span class="material-symbols-outlined">search</span><input
                                class="form-control" id="legacyGambitSearch" wire:model.live.debounce.300ms="search"
                                placeholder="Search 3,840 questions, clinical vignettes, pathologies, or ICD-10 keys...">
                            <div class="search-shortcuts"><kbd>⌘K</kbd><kbd>/</kbd></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <section class="active-strip">
            <div class="gambit-container container-line">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="active-title ps-4"><span class="material-symbols-outlined">tune</span>Active gambits
                        <span class="badge text-bg-primary">{{ $this->activeFilterCount }} criteria</span>
                    </div>
                    @if (count($selectedQuestions) > 0)
                        <button class="gambit-active-selection" type="button" @click="quizModalOpen = true">
                            <span class="material-symbols-outlined">checklist</span>
                            <span>{{ count($selectedQuestions) }} selected</span>
                        </button>
                    @endif
                    @if ($search !== '')
                        <span class="filter-chip">Search: {{ Str::limit($search, 24) }}
                            <button type="button" wire:click="$set('search', '')"
                                aria-label="Remove search filter">&times;</button>
                        </span>
                    @endif
                    @if ($difficulty !== '')
                        <span class="filter-chip">{{ ucfirst($difficulty) }}
                            <button type="button" wire:click="setDifficulty('')"
                                aria-label="Remove difficulty filter">&times;</button>
                        </span>
                    @endif
                    @if ($length !== '')
                        <span class="filter-chip">{{ ucfirst($length) }} 
                            <button type="button" wire:click="setLength('')"
                                aria-label="Remove length filter">&times;</button>
                        </span>
                    @endif
                    @foreach ($this->activeSpecialties as $specialty)
                        <span class="filter-chip">{{ $specialty->name }}
                            <button type="button" wire:click="removeSpecialty({{ $specialty->id }})"
                                aria-label="Remove {{ $specialty->name }} filter">&times;</button>
                        </span>
                    @endforeach
                    @foreach ($this->activeReferences as $reference)
                        <span class="filter-chip">{{ $reference->name }}
                            <button type="button" wire:click="removeReference({{ $reference->id }})"
                                aria-label="Remove {{ $reference->name }} filter">&times;</button>
                        </span>
                    @endforeach
                    @foreach ($this->activeBranches as $branch)
                        <span class="filter-chip">{{ $branch->name }}
                            <button type="button" wire:click="removeBranch({{ $branch->id }})"
                                aria-label="Remove {{ $branch->name }} filter">&times;</button>
                        </span>
                    @endforeach
                    @foreach ($this->activeSkills as $skill)
                        <span class="filter-chip">{{ $skill->name }}
                            <button type="button" wire:click="removeSkill({{ $skill->id }})"
                                aria-label="Remove {{ $skill->name }} filter">&times;</button>
                        </span>
                    @endforeach
                   
                </div>
                <div class="d-flex gap-3 px-4"><button class="btn btn-link btn-sm text-danger pe-4 gambit-clear-filters"
                        type="button" id="clearFilters" wire:click="clearFilters"><span
                            class="material-symbols-outlined align-middle">restart_alt</span> Clear</button></div>
            </div>
        </section>
        <div class="gambit-container"><button class="btn btn-outline-secondary mobile-filters" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#filtersPanel"><span
                    class="material-symbols-outlined align-middle me-1">filter_alt</span> Refine bank</button>
            <div class="workspace">
                <aside class="filter-panel">
                    <div class="filter-heading"><span><span
                                class="material-symbols-outlined align-middle me-1">filter_alt</span> Refine
                            bank</span><small>{{ $this->questions->total() }} found</small></div>
                    <div class="filter-group"><span class="filter-label">Difficulty profile</span>
                        <div class="filter-grid">
                            @foreach (['easy' => 'Easy', 'medium' => 'Med', 'hard' => 'Hard', 'nerd' => 'Nerd'] as $value => $label)
                                <button class="filter-option {{ $difficulty === $value ? 'active' : '' }}"
                                    type="button" wire:click="setDifficulty('{{ $value }}')"
                                    aria-pressed="{{ $difficulty === $value ? 'true' : 'false' }}">
                                    {{ $label }} <small>{{ $this->difficultyCounts->get($value, 0) }}</small>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="filter-group"><span class="filter-label">Question length</span>
                        <div class="filter-list">
                            @foreach (['short' => 'Short (<25 words)', 'medium' => 'Medium (25–45 words)', 'long' => 'Long (>45 words)'] as $value => $label)
                                <button class="filter-list-option {{ $length === $value ? 'active' : '' }}"
                                    type="button" wire:click="setLength('{{ $value }}')"
                                    aria-pressed="{{ $length === $value ? 'true' : 'false' }}">
                                    <span>{{ $label }}</span><small>{{ $this->lengthCounts->get($value, 0) }}</small>
                                </button>
                            @endforeach
                        </div>
                    </div>
                  
                </aside>
                <section class="results">
                    <div class="results-toolbar">
                        <div class="results-count">{{ $this->questions->total() }} questions found
                            <small>Showing
                                {{ $this->questions->firstItem() ?? 0 }}–{{ $this->questions->lastItem() ?? 0 }}</small>
                        </div>
                        <div class="gambit-search gambit-toolbar-search"><span
                                class="material-symbols-outlined">search</span><input class="form-control"
                                id="gambitSearch" wire:model.live.debounce.300ms="search"
                                placeholder="Search questions name or content">
                        </div>
                        <div class="gambit-advanced-filter" wire:ignore x-data="{ open: false }">
                            <button class="gambit-filter-trigger" type="button" @click="open = !open"
                                :aria-expanded="open.toString()" aria-controls="gambitAdvancedFilters"
                                aria-label="Open advanced filters">
                                <span class="material-symbols-outlined">filter_alt</span>
                                <span class="gambit-filter-trigger-count"
                                    x-show="{{ $this->activeFilterCount > 0 ? 'true' : 'false' }}">{{ $this->activeFilterCount }}</span>
                            </button>
                            <div id="gambitAdvancedFilters" class="gambit-filter-popover" x-cloak x-show="open"
                                x-transition @click.outside="open = false">
                                <div class="gambit-filter-popover-heading">
                                    <span>Advanced filters</span>
                                    <button type="button" @click="open = false"
                                        aria-label="Close filters">&times;</button>
                                </div>
                                <div class="gambit-select2-field">
                                    <label for="gambit-filter-specialties">Specialties</label>
                                    <select id="gambit-filter-specialties" multiple>
                                        @foreach ($this->activeSpecialties as $specialty)
                                            <option value="{{ $specialty->id }}" selected>{{ $specialty->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="gambit-select2-field">
                                    <label for="gambit-filter-branches">Branches</label>
                                    <select id="gambit-filter-branches" multiple>
                                        @foreach ($this->activeBranches as $branch)
                                            <option value="{{ $branch->id }}" selected>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="gambit-select2-field">
                                    <label for="gambit-filter-skills">Skills</label>
                                    <select id="gambit-filter-skills" multiple>
                                        @foreach ($this->activeSkills as $skill)
                                            <option value="{{ $skill->id }}" selected>{{ $skill->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="gambit-select2-field">
                                    <label for="gambit-filter-references">References</label>
                                    <select id="gambit-filter-references" multiple>
                                        @foreach ($this->activeReferences as $reference)
                                            <option value="{{ $reference->id }}" selected>{{ $reference->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-secondary small" for="gambitSort">Sort:</label>
                            <select id="gambitSort" class="form-select form-select-sm w-auto" wire:model.live="sort">
                                <option value="created_at">Newest cases</option>
                                <option value="difficulty">Difficulty</option>
                                <option value="length">Length</option>
                                <option value="name">Name</option>
                                <option value="updated_at">Recently updated</option>
                            </select>
                            <select class="form-select form-select-sm w-auto" wire:model.live="count"
                                aria-label="Questions per page">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                    <div id="questionList">
                        @forelse ($this->questions as $question)
                            <article
                                class="question-card {{ in_array($question->id, $selectedQuestions, true) ? 'selected' : '' }}">
                                <div class="d-flex gap-3">
                                    <input class="form-check-input mt-1" type="checkbox" @checked(in_array($question->id, $selectedQuestions, true))
                                        wire:click="toggleQuestion({{ $question->id }})" wire:loading.attr="disabled"
                                        aria-label="Select {{ $question->name }}">
                                    <div>
                                        <div class="question-tags">
                                            @foreach ($question->specialties as $specialty)
                                                <span class="badge">{{ $specialty->name }}</span>
                                            @endforeach
                                            @foreach ($question->branches as $branch)
                                                <span class="badge">{{ $branch->name }}</span>
                                            @endforeach
                                            @foreach ($question->skills as $skill)
                                                <span class="badge primary">{{ $skill->name }}</span>
                                            @endforeach
                                            @if ($question->reference)
                                                <span class="badge">{{ $question->reference->name }}</span>
                                            @endif
                                            <span class="gambit-meta">QID #{{ $question->id }}</span>
                                        </div>
                                        <h2>{{ $question->name }}</h2>
                                        <p>{{ Str::limit(strip_tags($question->content), 220) }}</p>
                                        <div class="question-metrics">
                                            <span class="{{ $question->difficulty === 'hard' ? 'hard' : '' }}">●
                                                {{ ucfirst($question->difficulty) }}</span>
                                            <span>{{ ucfirst($question->length) }} </span>
                                            <span class="gain">+{{ $question->elo_correct }} ELO</span>
                                            <span class="text-danger">-{{ $question->elo_incorrect }} Elo </span>

                                            <span>{{ $question->playedCount?->count ?? 0 }} plays</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="question-action">
                                    <button class="btn btn-primary" type="button"
                                        wire:click="tryQuestion({{ $question->id }})">Try question <span
                                            class="material-symbols-outlined align-middle ms-1">arrow_forward</span></button>
                                    <small>Instant turn</small>
                                </div>
                            </article>
                        @empty
                            <div class="results-toolbar"><span class="text-secondary">No questions match these
                                    criteria.</span></div>
                        @endforelse
                    </div>
                    <div class="pagination-wrap">
                        <span class="gambit-meta">Showing
                            {{ $this->questions->firstItem() ?? 0 }}–{{ $this->questions->lastItem() ?? 0 }} of
                            {{ $this->questions->total() }} questions</span>
                        {{ $this->questions->links('vendor.pagination.medgambit') }}
                    </div>

                </section>
            </div>
        </div>
    </main>

    @if (count($selectedQuestions) > 0)
        <button class="gambit-start-quiz-button" type="button" @click="quizModalOpen = true">
            <span class="material-symbols-outlined">bolt</span>
            <span>Start Quiz</span>
            <span class="gambit-selected-count">{{ count($selectedQuestions) }}</span>
        </button>
    @endif

    <div class="gambit-quiz-modal" x-cloak x-show="quizModalOpen" x-transition role="dialog" aria-modal="true"
        aria-labelledby="gambitQuizModalTitle" tabindex="-1">
        <button class="gambit-quiz-modal-backdrop" type="button" aria-label="Close quiz setup"
            @click="quizModalOpen = false"></button>
        <div class="gambit-quiz-modal-dialog">
            <div class="gambit-quiz-modal-content">
                <div class="gambit-quiz-modal-header">
                    <div>
                        <span class="gambit-modal-kicker">Gambit protocol</span>
                        <h2 id="gambitQuizModalTitle">Start quiz</h2>
                        <p>{{ count($selectedQuestions) }} question{{ count($selectedQuestions) === 1 ? '' : 's' }}
                            selected</p>
                    </div>
                    <button class="gambit-modal-close" type="button" aria-label="Close quiz setup"
                        @click="quizModalOpen = false">&times;</button>
                </div>
                <div class="gambit-quiz-modal-body">
                    <label class="form-label" for="gambitQuizName">Quiz name</label>
                    <input id="gambitQuizName" type="text" class="form-control" wire:model="quizName"
                        placeholder="e.g. Cardiology review" autofocus>
                    @error('quizName')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('selectedQuestions')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <p class="gambit-modal-help">Choose how you want to work through these questions.</p>
                    <div class="gambit-mode-grid">
                        <button class="gambit-mode-option" type="button" wire:click="learningQuiz">
                            <span class="material-symbols-outlined">school</span>
                            <span><strong>Learning mode</strong><small>Get feedback as you practice.</small></span>
                            <span class="material-symbols-outlined gambit-mode-arrow">arrow_forward</span>
                        </button>
                        <button class="gambit-mode-option" type="button" wire:click="examQuiz">
                            <span class="material-symbols-outlined">timer</span>
                            <span><strong>Exam mode</strong><small>Complete the set under exam
                                    conditions </small><span><small class="text-danger">
                                        This Mode Will Effect You Elo</small></span></span>
                            <span class="material-symbols-outlined gambit-mode-arrow">arrow_forward</span>
                        </button>
                    </div>
                </div>
                <div class="gambit-quiz-modal-footer">
                    <button class="btn btn-outline-secondary" type="button"
                        @click="quizModalOpen = false">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        const initializeGambitsSelects = () => {
            const select2 = window.MedGambitSelect2;
            const filterPanel = document.getElementById('gambitAdvancedFilters');

            if (!select2 || !filterPanel) {
                return;
            }

            const selectedIds = (value) => value.map(Number);
            const controls = {
                branches: '#gambit-filter-branches',
                specialties: '#gambit-filter-specialties',
                skills: '#gambit-filter-skills',
                references: '#gambit-filter-references',
            };

            select2.init(controls.specialties, {
                ajaxUrl: @js(route('getSpeciality')),
                placeholder: 'Search specialties',
                dropdownParent: filterPanel,
                onChange: (value) => $wire.set('sp', selectedIds(value)),
            });
            select2.init(controls.branches, {
                ajaxUrl: @js(route('getBranches')),
                placeholder: 'Search branches',
                dropdownParent: filterPanel,
                onChange: (value) => $wire.set('branches', selectedIds(value)),
            });
            select2.init(controls.skills, {
                ajaxUrl: @js(route('getSkills')),
                placeholder: 'Search skills',
                dropdownParent: filterPanel,
                onChange: (value) => $wire.set('skills', selectedIds(value)),
            });
            select2.init(controls.references, {
                ajaxUrl: @js(route('getReferences')),
                placeholder: 'Search references',
                dropdownParent: filterPanel,
                onChange: (value) => $wire.set('references', selectedIds(value)),
            });

            Livewire.on('gambits-select2-sync', (filters) => {
                Object.entries(controls).forEach(([filter, selector]) => {
                    window.jQuery(selector).val(filters[filter] || []).trigger('change.select2');
                });
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeGambitsSelects, {
                once: true
            });
        } else {
            initializeGambitsSelects();
        }
    </script>
@endscript
