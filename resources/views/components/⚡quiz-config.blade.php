<?php

use App\Models\Questions;
use App\Services\QuizService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public int $count = 20;
    public array $branchesList = [];

    public array $references = [];

    public array $specialitiesList = [];

    public array $skillsList = [];

    public string $difficulty = 'medium';

    public string $length = 'medium';

    public $duration;

    private const QUESTION_COUNTS = [5, 10, 15, 20];

    #[Computed]
    public function questionBankCount(): int
    {
        return $this->questions?->count() ?? 0;
    }

    #[Computed]
    public function playedQuestionsCount(): int
    {
        return auth()->user()->playedQuestions()->count();
        // return
    }

    private function applyFilters($query)
    {
        return $query
            ->when($this->difficulty, fn($query) => $query->where('difficulty', $this->difficulty))
            ->when($this->length, fn($query) => $query->where('length', $this->length))
            ->when($this->branchesList, function ($query) {
                $query->whereHas('branches', fn($query) => $query->whereIn('branch_of_medicines.id', $this->branchesList));
            })
            ->when($this->references, fn($query) => $query->whereIn('reference_id', $this->references))
            ->when($this->skillsList, function ($query) {
                $query->whereHas('skills', fn($query) => $query->whereIn('skills_for_questions.id', $this->skillsList));
            })
            ->when($this->specialitiesList, function ($query) {
                $query->whereHas('specialties', fn($query) => $query->whereIn('specialties.id', $this->specialitiesList));
            });
    }

    public function setDifficulty(string $difficulty): void
    {
        if (in_array($difficulty, ['easy', 'medium', 'hard', 'nerd'], true)) {
            $this->difficulty = $difficulty;
        }
    }

    public function setLength(string $length): void
    {
        if (in_array($length, ['short', 'medium', 'long'], true)) {
            $this->length = $length;
        }
    }

    public function updatedCount($count): void
    {
        $count = (int) $count;
        $this->count = in_array($count, self::QUESTION_COUNTS, true) ? $count : 20;
    }

    #[Computed]
    public function questions()
    {
        if (empty($this->difficulty) && empty($this->length) && empty($this->branchesList) && empty($this->skillsList) && empty($this->specialitiesList) && empty($this->references)) {
            return null;
        }

        $questionLimit = min(max((int) $this->count, 2), 20);
        $userPlayedQuestion = auth()->user()->playedQuestions()->pluck('questions_id');
        $questions = $this->applyFilters(Questions::query()->when($userPlayedQuestion->isNotEmpty(), fn($query) => $query->whereNotIn('id', $userPlayedQuestion)))
            ->limit($questionLimit)
            ->get();

        if ($questions->count() < $questionLimit) {
            $fallback = $this->applyFilters(Questions::query())
                ->whereIn('id', $userPlayedQuestion)
                ->whereNotIn('id', $questions->pluck('id'))
                ->limit($questionLimit - $questions->count())
                ->get();

            $questions = $questions->merge($fallback);
        }

        return $questions;
    }

    private function validationRules(): array
    {
        return [
            'count' => ['required', 'integer', Rule::in(self::QUESTION_COUNTS)],
            'difficulty' => ['nullable', Rule::in(['easy', 'medium', 'hard', 'nerd'])],
            'length' => ['nullable', Rule::in(['short', 'medium', 'long'])],
            'branchesList' => ['nullable', 'array'],
            'branchesList.*' => ['exists:branch_of_medicines,id'],
            'skillsList' => ['nullable', 'array'],
            'skillsList.*' => ['exists:skills_for_questions,id'],
            'specialitiesList' => ['nullable', 'array'],
            'specialitiesList.*' => ['exists:specialties,id'],
            'references' => ['nullable', 'array'],
            'references.*' => ['exists:references,id'],
        ];
    }

    private function ensureQuestionsAreAvailable(): void
    {
        if ($this->questions?->count() < 2) {
            throw ValidationException::withMessages([
                'count' => 'At least 2 questions are required.',
            ]);
        }
    }

    public function submit()
    {
        $this->validate($this->validationRules());
        $this->ensureQuestionsAreAvailable();

        $quiz = app(QuizService::class)->detectedQuiz(questions: $this->questions, length: $this->length ?: 'medium', difficulty: $this->difficulty ?: 'medium', duration: $this->duration, count: $this->questions->count());

        return redirect()->route('start.detecated.quiz', $quiz);
    }

    public function learningQuiz()
    {
        $this->validate($this->validationRules());
        $this->ensureQuestionsAreAvailable();

        $quiz = app(QuizService::class)->learningQuiz(questions: $this->questions, length: $this->length ?: 'short', count: $this->questions->count(), difficulty: $this->difficulty ?: 'hard');

        return redirect()->route('start.learning.quiz', $quiz);
    }
};
?>

<div>
    <main class="quiz-main">
        <aside class="quiz-sidebar">
            <div class="quiz-sidebar-title">Quiz builder</div>
            <h2>Create Quiz</h2>
            <nav class="quiz-nav">
                <a class="active" href="{{ route('start.quiz') }}">
                    New Quiz <span class="badge text-bg-primary">READY</span>
                </a>
                <a href="{{ route('user.profile', auth()->user()) }}">
                    Played Questions <span class="badge text-bg-secondary">{{ $this->playedQuestionsCount }}</span>
                </a>
                <a href="{{ route('user.profile', auth()->user()) }}">
                    Review History <span class="badge text-bg-secondary">VIEW</span>
                </a>
            </nav>
            <div class="quiz-server">
                <small>QUESTION BANK</small>
                <strong>{{ $this->questionBankCount }} QUESTIONS</strong>
                <small class="mt-2"></small>
            </div>
        </aside>

        <div class="quiz-content">
            <button class="btn btn-outline-secondary mobile-quiz-sidebar" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#quizSidebar">
                <span class="material-symbols-outlined align-middle me-1">tune</span>Quiz builder
            </button>

            <div class="quiz-card">
                <div class="quiz-label">Quiz builder · targeted practice</div>
                <h1 class="quiz-title">Customize Your Quizzes</h1>
                <p>There a two different modes of quizzes
                </p>
                <ol>
                    <li> Learning Mode Without Elo
                    </li>
                    <li>Exam Mode With Elo Changes </li>
                </ol>
                <form id="quizForm" wire:submit.prevent="submit">
                    <div class="quiz-field">
                        <span class="quiz-label">Difficulty</span>
                        <div class="quiz-choice-group four" role="radiogroup" aria-label="Difficulty">
                            @foreach (['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard', 'nerd' => 'Nerd'] as $value => $label)
                                <button class="quiz-choice {{ $difficulty === $value ? 'active' : '' }}" type="button"
                                    wire:click="setDifficulty('{{ $value }}')"
                                    aria-pressed="{{ $difficulty === $value ? 'true' : 'false' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="quiz-field">
                        <span class="quiz-label">Question length</span>
                        <div class="quiz-choice-group three" role="radiogroup" aria-label="Question length">
                            @foreach (['short' => 'Short · 10', 'medium' => 'Medium · 25', 'long' => 'Long · 50'] as $value => $label)
                                <button class="quiz-choice {{ $length === $value ? 'active' : '' }}" type="button"
                                    wire:click="setLength('{{ $value }}')"
                                    aria-pressed="{{ $length === $value ? 'true' : 'false' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="quiz-field">
                        <label for="branches" class="quiz-label">Branches</label>
                        <div class="quiz-select2-wrapper" wire:ignore>
                            <select id="branches" class="form-select" multiple></select>
                        </div>
                        @error('branchesList')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="quiz-field">
                        <label for="sp" class="quiz-label">Specialties</label>
                        <div class="quiz-select2-wrapper" wire:ignore>
                            <select id="sp" class="form-select" multiple></select>
                        </div>
                        @error('specialitiesList')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="quiz-field">
                        <label for="skills" class="quiz-label">Skills</label>
                        <div class="quiz-select2-wrapper" wire:ignore>
                            <select id="skills" class="form-select" multiple></select>
                        </div>
                        @error('skillsList')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="quiz-field">
                        <label for="references" class="quiz-label">References</label>
                        <div class="quiz-select2-wrapper" wire:ignore>
                            <select id="references" class="form-select" multiple></select>
                        </div>
                        @error('references')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row align-items-center">

                        <div class="quiz-field col-lg-6 col-sm-12">
                            <label for="duration" class="quiz-label">Quiz timer</label>
                            <div wire:ignore>
                                <select id="duration" class="form-select">
                                    <option value="">No duration</option>
                                    <option value="300">5 minutes</option>
                                    <option value="600">10 minutes</option>
                                    <option value="900">15 minutes</option>
                                    <option value="1200">20 minutes</option>
                                    <option value="1800">30 minutes</option>
                                    <option value="3600">60 minutes</option>
                                </select>
                            </div>
                        </div>
                        <div class=" quiz-field col-lg-6 col-sm-12">
                            <label for="count" class="quiz-label">Count Of Questions</label>

                            <select wire:model.live="count" class="form-select">
                                <option value="20">20</option>
                                <option value="15">15</option>
                                <option value="10">10</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                    </div>


                    <div class="quiz-options align-items-center">
                        <span class="quiz-option justify-content-start">
                            <span class="material-symbols-outlined text-primary me-2">filter_alt</span>
                            <span>{{ $this->questionBankCount }} matching questions</span>
                        </span>
                        <button class="btn btn-primary btn-lg" type="submit" wire:loading.attr="disabled">
                            <span class="material-symbols-outlined align-middle me-2">quiz</span>Create quiz
                        </button>
                    </div>

                    @error('count')
                        <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
                    @enderror

                    <span class="bg-warning btn      w-100 mt-3" type="button" wire:click="learningQuiz"
                        wire:loading.attr="disabled">
                        Start learning quiz
                    </span>
                </form>
            </div>
        </div>
    </main>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="quizSidebar">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Quiz builder</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="quiz-sidebar-title">Quiz builder</div>
            <nav class="quiz-nav mt-3">
                <a class="active" href="{{ route('start.quiz') }}">
                    New Quiz <span class="badge text-bg-primary">READY</span>
                </a>
                <a href="{{ route('user.profile', auth()->user()) }}">
                    Played Questions <span class="badge text-bg-secondary">{{ $this->playedQuestionsCount }}</span>
                </a>
                <a href="{{ route('user.profile', auth()->user()) }}">
                    Review History <span class="badge text-bg-secondary">VIEW</span>
                </a>
            </nav>
        </div>
    </div>
</div>

@script
    <script>
        const initializeQuizConfigSelects = () => {
            const select2 = window.MedGambitSelect2;

            if (!select2) {
                return;
            }

            const selectedIds = (value) => value.map(Number);

            select2.init('#branches', {
                ajaxUrl: @js(route('getBranches')),
                placeholder: 'Search branches',
                onChange: (value) => $wire.set('branchesList', selectedIds(value)),
            });
            select2.init('#sp', {
                ajaxUrl: @js(route('getSpeciality')),
                placeholder: 'Search specialties',
                onChange: (value) => $wire.set('specialitiesList', selectedIds(value)),
            });
            select2.init('#skills', {
                ajaxUrl: @js(route('getSkills')),
                placeholder: 'Search skills',
                onChange: (value) => $wire.set('skillsList', selectedIds(value)),
            });
            select2.init('#references', {
                ajaxUrl: @js(route('getReferences')),
                placeholder: 'Search references',
                onChange: (value) => $wire.set('references', selectedIds(value)),
            });
            select2.init('#duration', {
                placeholder: 'Select quiz timer',
                multiple: false,
                onChange: (value) => $wire.set('duration', value),
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeQuizConfigSelects, {
                once: true
            });
        } else {
            initializeQuizConfigSelects();
        }
    </script>
@endscript
