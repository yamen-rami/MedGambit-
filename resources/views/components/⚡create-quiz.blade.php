<?php

use App\Models\Questions;
use App\Models\Quiz;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    public string $search = '';
    public array $specialties = [],
        $branches = [],
        $skills = [];
    public string $length = '',
        $difficulty = '';
    public int $questionNumber = 3;
    public string $quizTopic = '',
        $quizDifficulty = 'easy',
        $quizLength = 'short',
        $quizName = '';
    public string $sort = 'desc';
    public array $results = [];

    #[Computed]
    public function questions()
    {
        return Questions::query()
            ->when(
                $this->search !== '',
                fn($q) => $q->where(
                    fn($q) => $q
                        ->where('content', 'like', "%{$this->search}%")
                        ->orWhere('topic', 'like', "%{$this->search}%")
                        ->orWhere('name', 'like', "%{$this->search}%"),
                ),
            )
            ->when($this->length, fn($q) => $q->where('length', $this->length))
            ->when($this->difficulty, fn($q) => $q->where('difficulty', $this->difficulty))
            ->when($this->specialties, fn($q) => $q->whereHas('specialties', fn($q) => $q->whereIn('specialties.id', $this->specialties)))
            ->when($this->branches, fn($q) => $q->whereHas('branches', fn($q) => $q->whereIn('branch_of_medicines.id', $this->branches)))
            ->when($this->skills, fn($q) => $q->whereHas('skills', fn($q) => $q->whereIn('skills_for_questions.id', $this->skills)))
            ->latest('id')
            ->paginate(10);
    }
    public function add(int $questionId): void
    {
        if ($this->questionNumber >= 3 && $this->questionNumber <= 20 && count($this->results) < $this->questionNumber) {
            $this->results[$questionId] = true;
        }
    }
    public function remove(int $questionId): void
    {
        unset($this->results[$questionId]);
    }
    public function clear(): void
    {
        $this->reset(['search', 'length', 'difficulty', 'specialties', 'branches', 'skills']);
        $this->resetPage();
        $this->dispatch('quiz-filters-cleared');
    }
    public function updatedQuestionNumber($value): void
    {
        $this->questionNumber = max(3, min(20, (int) $value));
        $this->results = array_slice($this->results, 0, $this->questionNumber, true);
    }
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedLength(): void
    {
        $this->resetPage();
    }
    public function updatedDifficulty(): void
    {
        $this->resetPage();
    }
    public function updatedSpecialties(): void
    {
        $this->resetPage();
    }
    public function updatedBranches(): void
    {
        $this->resetPage();
    }
    public function updatedSkills(): void
    {
        $this->resetPage();
    }
    public function save()
    {
        $data = $this->validate(['questionNumber' => ['required', 'integer', 'min:3', 'max:20'], 'quizName' => ['required', 'string', 'min:3'], 'quizTopic' => ['required', 'string', 'min:3'], 'quizDifficulty' => ['required', Rule::in(['easy', 'medium', 'hard', 'nerd'])], 'quizLength' => ['required', Rule::in(['short', 'medium', 'long'])], 'results' => ['required', 'array', 'min:3', 'max:20']]);
        $quiz = Quiz::create(['name' => $data['quizName'], 'questions_number' => $data['questionNumber'], 'type' => 'admin', 'topic' => $data['quizTopic'], 'difficulty' => $data['quizDifficulty'], 'length' => $data['quizLength']]);
        $quiz->questions()->attach(array_keys($this->results));
        flash()->success('Quiz Has Created Successfully');
        return redirect()->route('quizez.index');
    }
};
?>

<div class="container-fluid px-0">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white">
            <h1 class="h4 mb-1 text-white">Create quiz</h1>
            <p class="mb-0 text-white-50">Create a quiz and choose between 3 and 20 questions.</p>
        </div>
        <form wire:submit="save">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12 col-xl-4">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary-subtle">
                                <h2 class="h5 mb-0">Quiz details</h2>
                            </div>
                            <div class="card-body">
                                <div class="mb-3"><label class="form-label" for="create-quiz-name">Quiz
                                        name</label><input id="create-quiz-name" wire:model="quizName"
                                        class="form-control" placeholder="e.g. Cardiology essentials">
                                    @error('quizName')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3"><label class="form-label" for="create-quiz-topic">Topic</label>
                                    <textarea id="create-quiz-topic" wire:model="quizTopic" class="form-control" rows="3"
                                        placeholder="What should learners practice?"></textarea>
                                    @error('quizTopic')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="row g-2">
                                    <div class="col-6 mb-3"><label class="form-label"
                                            for="create-question-number">Questions</label><input
                                            id="create-question-number" wire:model.live="questionNumber" type="number"
                                            min="3" max="20" class="form-control">
                                        @error('questionNumber')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6 mb-3"><label class="form-label"
                                            for="create-difficulty">Difficulty</label><select id="create-difficulty"
                                            wire:model="quizDifficulty" class="form-select">
                                            <option value="easy">Easy</option>
                                            <option value="medium">Medium</option>
                                            <option value="hard">Hard</option>
                                            <option value="nerd">Nerd</option>
                                        </select></div>
                                </div>
                                <div class="mb-3"><label class="form-label" for="create-length">Length</label><select
                                        id="create-length" wire:model="quizLength" class="form-select">
                                        <option value="short">Short</option>
                                        <option value="medium">Medium</option>
                                        <option value="long">Long</option>
                                    </select></div>
                                <div
                                    class="border-top pt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <span class="text-body-secondary">{{ count($results) }} / {{ $questionNumber }}
                                        selected</span><button class="btn btn-primary" type="submit">Create
                                        quiz</button>
                                </div>
                                @error('results')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-8">
                        <div class="card h-100">
                            <div class="card-header">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <div>
                                        <h2 class="h5 mb-1">Choose questions</h2>
                                        <p class="text-body-secondary mb-0">Filter the question bank and add questions
                                            to this quiz.</p>
                                    </div><span
                                        class="badge text-bg-primary align-self-start">{{ $this->questions->total() }}
                                        results</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-6"><label class="form-label">Difficulty</label><select
                                            wire:model.live.debounce.400ms="difficulty" class="form-select">
                                            <option value="">All difficulties</option>
                                            <option value="easy">Easy</option>
                                            <option value="medium">Medium</option>
                                            <option value="hard">Hard</option>
                                            <option value="nerd">Nerd</option>
                                        </select></div>
                                    <div class="col-12 col-md-6"><label class="form-label">Length</label><select
                                            wire:model.live.debounce.400ms="length" class="form-select">
                                            <option value="">All lengths</option>
                                            <option value="short">Short</option>
                                            <option value="medium">Medium</option>
                                            <option value="long">Long</option>
                                        </select></div>
                                    <div class="col-12 col-md-6" wire:ignore><label class="form-label"
                                            for="create-quiz-branches">Branches</label>
                                            <select id="create-quiz-branches"
                                            class="select2 form-select" data-livewire-select2 multiple>
                                            </select>
                                        </div>
                                    <div class="col-12 col-md-6" wire:ignore><label class="form-label"
                                            for="create-quiz-specialties">Specialties</label><select
                                            id="create-quiz-specialties" class="select2 form-select"
                                            data-livewire-select2 multiple></select></div>
                                    <div class="col-12 col-md-6" wire:ignore><label class="form-label"
                                            for="create-quiz-skills">Skills</label><select id="create-quiz-skills"
                                            class="select2 form-select" data-livewire-select2 multiple></select></div>
                                    <div class="col-12 col-md-6 d-flex align-items-end"><button type="button"
                                            wire:click="clear" class="btn btn-outline-secondary w-100">Clear
                                            filters</button></div>
                                </div>
                                <input wire:model.live.debounce.400ms="search" class="form-control mb-3"
                                    placeholder="Search question text, topic, or name…">
                                <div class="small text-body-secondary mb-2">Showing
                                    {{ $this->questions->firstItem() ?? 0 }}–{{ $this->questions->lastItem() ?? 0 }} of
                                    {{ $this->questions->total() }}</div>
                                @forelse ($this->questions as $question)
                                    <div
                                        class="border rounded p-3 mb-2 d-flex flex-column flex-sm-row justify-content-between gap-3">
                                        <div class="text-break">
                                            <div class="fw-semibold">
                                                {{ $question->name ?: Str::limit($question->content, 100) }}</div>
                                            <div class="small text-body-secondary">
                                                {{ Str::limit($question->content, 120) }} ·
                                                {{ ucfirst($question->difficulty) }} ·
                                                {{ ucfirst($question->length) }}</div>
                                        </div><button type="button"
                                            wire:click="{{ array_key_exists($question->id, $results) ? 'remove' : 'add' }}({{ $question->id }})"
                                            class="btn btn-sm {{ array_key_exists($question->id, $results) ? 'btn-outline-danger' : 'btn-outline-primary' }} align-self-start">{{ array_key_exists($question->id, $results) ? 'Remove' : 'Add' }}</button>
                                </div>@empty<div class="text-center text-body-secondary py-5">No questions match
                                        these filters.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@script
    <script>
        const branches = $('#create-quiz-branches');
        const specialties = $('#create-quiz-specialties');
        const skills = $('#create-quiz-skills');

        branches.select2({
            width: '100%',
            placeholder: 'Search for Branches',
            ajax: {
                url: "{{ route('getBranches') }}",
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    search: params.term || ''
                }),
                processResults: (data) => ({
                    results: data.map((branch) => ({
                        id: branch.id,
                        text: branch.name
                    }))
                }),
            },
        }).on('change', function() {
            $wire.set('branches', $(this).val() || [], true);
        });

        specialties.select2({
            width: '100%',
            placeholder: 'Search for Specialities',
            ajax: {
                url: "{{ route('getSpeciality') }}",
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    search: params.term || ''
                }),
                processResults: (data) => ({
                    results: data.map((specialty) => ({
                        id: specialty.id,
                        text: specialty.name
                    }))
                }),
            },
        }).on('change', function() {
            $wire.set('specialties', $(this).val() || [], true);
        });

        skills.select2({
            width: '100%',
            placeholder: 'Search for Skills',
            ajax: {
                url: "{{ route('getSkills') }}",
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    search: params.term || ''
                }),
                processResults: (data) => ({
                    results: data.map((skill) => ({
                        id: skill.id,
                        text: skill.name
                    }))
                }),
            },
        }).on('change', function() {
            $wire.set('skills', $(this).val() || [], true);
        });

        $wire.on('quiz-filters-cleared', () => {
            branches.val(null).trigger('change.select2');
            specialties.val(null).trigger('change.select2');
            skills.val(null).trigger('change.select2');
        });
    </script>
@endscript
