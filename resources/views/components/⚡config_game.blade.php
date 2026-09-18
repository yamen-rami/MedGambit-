<?php

use App\Models\Questions;
use App\Models\{GameAttempt, Game};
use App\Services\GameService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public array $branchesList = [];
    public array $specialitiesList = [];
    public array $skillsList = [];
    public array $references = [];
    public string $difficulty = 'medium';
    public string $length = 'medium';
    public $duration;
    public int $count = 20;

    private const QUESTION_COUNTS = [5, 10, 15, 20];

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
    public function playedGamesToday(): int
    {
        return Game::query()->whereDate('created_at', today())->count();
    }

    #[Computed]
    public function questionBankCount(): int
    {
        return $this->questions?->count() ?? 0;
    }

    #[Computed]
    public function questions()
    {
        if (empty($this->difficulty) && empty($this->length) && empty($this->branchesList) && empty($this->skillsList) && empty($this->specialitiesList) && empty($this->references)) {
            return null;
        }

        $questions = Questions::query()->when($this->references, fn($query) => $query->whereHas('reference', fn($query) => $query->whereIn('references.id', $this->references)))->when($this->difficulty, fn($query) => $query->where('difficulty', $this->difficulty))->when($this->length, fn($query) => $query->where('length', $this->length))->when($this->branchesList, fn($query) => $query->whereHas('branches', fn($query) => $query->whereIn('branch_of_medicines.id', $this->branchesList)))->when($this->skillsList, fn($query) => $query->whereHas('skills', fn($query) => $query->whereIn('skills_for_questions.id', $this->skillsList)))->when($this->specialitiesList, fn($query) => $query->whereHas('specialties', fn($query) => $query->whereIn('specialties.id', $this->specialitiesList)))->limit($this->count)->get();

        return $questions->isEmpty() ? null : $questions;
    }

    private function validationRules(): array
    {
        return [
            'difficulty' => ['nullable', Rule::in(['easy', 'medium', 'hard', 'nerd'])],
            'length' => ['nullable', Rule::in(['short', 'medium', 'long'])],
            'duration' => ['nullable', 'integer', 'in:300,600,900,1200,1800,3600'],
            'count' => ['required', 'integer', Rule::in(self::QUESTION_COUNTS)],
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

    public function friendGame()
    {
        $this->validate($this->validationRules());

        if ($this->questions?->count() < 2) {
            throw ValidationException::withMessages(['count' => 'At least 2 questions are required.']);
        }

        $game = app(GameService::class)->friendGame(difficulty: $this->difficulty, length: $this->length, duration: $this->duration, sp: $this->specialitiesList, count: $this->count, branches: $this->branchesList, skills: $this->skillsList, references: $this->references);
        return redirect()->route('friend.game.started', $game->challenge_token);
    }
};
?>

<div>
    <main class="quiz-main">
        <aside class="quiz-sidebar">
            <div class="quiz-sidebar-title">Arena match setup</div>
            <h2>Tactical Config</h2>
            <nav class="quiz-nav ">
                <a class="active " href="{{ route('config.game') }}">1v1 Ranked Duel <span
                        class="badge text-bg-primary">LIVE</span></a>
                <a href="#">Played Games Today <span
                        class="badge text-bg-secondary">{{ $this->playedGamesToday }}</span></a>
                <a href="#">Active Players <span class="badge text-bg-success"><x-online-users
                            id="game-online-users-desktop" /></span></a>

            </nav>
            <div class="quiz-server">
                <small>QUESTION BANK</small>
                <strong>{{ $this->questionBankCount }} MATCHING QUESTIONS</strong>
            </div>
        </aside>

        <div class="quiz-content">
            <button class="btn btn-outline-secondary mobile-quiz-sidebar" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#gameSidebar" aria-controls="gameSidebar">
                <span class="material-symbols-outlined align-middle me-1">tune</span>Match modes
            </button>

            <div class="quiz-card">
                <h1 class="quiz-title">Friend Game</h1>
                <div class="quiz-title fs-6 ">Customize you interest then share a link with your friend </div>

                <form id="gameConfigForm" wire:submit.prevent="friendGame">
                    <div class="quiz-field">
                        <span class="quiz-label">Difficulty</span>
                        <div class="quiz-choice-group four" role="radiogroup" aria-label="Difficulty">
                            @foreach (['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard', 'nerd' => 'Nerd'] as $value => $label)
                                <button class="quiz-choice {{ $difficulty === $value ? 'active' : '' }}" type="button"
                                    wire:click="setDifficulty('{{ $value }}')"
                                    aria-pressed="{{ $difficulty === $value ? 'true' : 'false' }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>



                    <div class="quiz-field">
                        <span class="quiz-label">Question length</span>
                        <div class="quiz-choice-group three" role="radiogroup" aria-label="Question length">
                            @foreach (['short' => 'Short', 'medium' => 'Medium', 'long' => 'Long'] as $value => $label)
                                <button class="quiz-choice {{ $length === $value ? 'active' : '' }}" type="button"
                                    wire:click="setLength('{{ $value }}')"
                                    aria-pressed="{{ $length === $value ? 'true' : 'false' }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    @foreach ([['id' => 'branches', 'label' => 'Branches', 'property' => 'branchesList', 'url' => 'getBranches'], ['id' => 'sp', 'label' => 'Specialties', 'property' => 'specialitiesList', 'url' => 'getSpeciality'], ['id' => 'skills', 'label' => 'Skills', 'property' => 'skillsList', 'url' => 'getSkills'], ['id' => 'references', 'label' => 'References', 'property' => 'references', 'url' => 'getReferences']] as $select)
                        <div class="quiz-field">
                            <label for="{{ $select['id'] }}" class="quiz-label">{{ $select['label'] }}</label>
                            <div class="quiz-select2-wrapper" wire:ignore><select id="{{ $select['id'] }}"
                                    class="form-select" multiple></select></div>
                            @error($select['property'])
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach

                    <div class="quiz-field">
                        <label for="duration" class="quiz-label">Game timer</label>
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
                        @error('duration')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="quiz-field">
                        <label for="count" class="quiz-label">Count of questions</label>
                        <select id="count" wire:model.live="count" class="form-select">
                            @foreach ([20, 15, 10, 5] as $questionCount)
                                <option value="{{ $questionCount }}">{{ $questionCount }}</option>
                            @endforeach
                        </select>
                        @error('count')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="quiz-options align-items-center">
                        <span class="quiz-option justify-content-start"><span
                                class="material-symbols-outlined text-primary me-2  ">filter_alt</span><span class="">
                                    <span class="fs-6 text-primary  ">
                                        {{ $this->questionBankCount }}
                                    </span>
                                matching questions</span></span>
                        <button class="btn btn-primary btn-lg" type="submit" wire:loading.attr="disabled"><span
                                class="material-symbols-outlined align-middle me-2">sports_esports</span>Friend Game</button>
                    </div>
                    @error('count')
                        <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
                    @enderror
                </form>
            </div>
        </div>
    </main>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="gameSidebar" aria-labelledby="gameSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="gameSidebarLabel">Tactical Config</h5><button type="button"
                class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="quiz-sidebar-title">Arena match setup</div>
            <nav class="quiz-nav mt-3">
                <a class="active" href="{{ route('config.game') }}">1v1 Ranked Duel <span
                        class="badge text-bg-primary">LIVE</span></a>
                <a href="#">Played Games Today <span
                        class="badge text-bg-secondary">{{ $this->playedGamesToday }}</span></a>
                <a href="#">Active Players <span class="badge text-bg-success"><x-online-users
                            id="game-online-users-mobile" /></span></a>

            </nav>
            <div class="quiz-server mt-4">
                <small>QUESTION BANK</small>
                <strong>{{ $this->questionBankCount }} MATCHING QUESTIONS</strong>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        const initializeConfigGameSelects = () => {
            const select2 = window.MedGambitSelect2;
            if (!select2) return;
            const selectedIds = (value) => value.map(Number);
            select2.init('#branches', {
                ajaxUrl: @js(route('getBranches')),
                placeholder: 'Search branches',
                onChange: (value) => $wire.set('branchesList', selectedIds(value))
            });
            select2.init('#sp', {
                ajaxUrl: @js(route('getSpeciality')),
                placeholder: 'Search specialties',
                onChange: (value) => $wire.set('specialitiesList', selectedIds(value))
            });
            select2.init('#skills', {
                ajaxUrl: @js(route('getSkills')),
                placeholder: 'Search skills',
                onChange: (value) => $wire.set('skillsList', selectedIds(value))
            });
            select2.init('#references', {
                ajaxUrl: @js(route('getReferences')),
                placeholder: 'Search references',
                onChange: (value) => $wire.set('references', selectedIds(value))
            });
            select2.init('#duration', {
                placeholder: 'Select game timer',
                multiple: false,
                onChange: (value) => $wire.set('duration', value || null)
            });
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeConfigGameSelects, {
            once: true
        });
        else initializeConfigGameSelects();
    </script>
@endscript
