<?php

use App\Models\Questions;
use App\Services\GameService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public array $branchesList = [];

    public array $specialitiesList = [];

    public array $skillsList = [];

    public string $difficulty = '' ;

    public string $length = '';

    public $duration;

    public $branches;

    public $skills;
    public array $references = [];

    public $specialities;

    #[Computed]
    public function questions()
    {
        if (empty($this->difficulty) && empty($this->length) && empty($this->branchesList) && empty($this->skillsList) && empty($this->specialitiesList) && empty($this->references)) {
            return null;
        }

        $questions = Questions::query()
            ->when($this->references, function ($query) {
                $query->whereHas('reference', fn($q) => $q->whereIn('references.id', $this->references));
            })
            ->when($this->difficulty, fn($query) => $query->where('difficulty', $this->difficulty))
            ->when($this->length, fn($query) => $query->where('length', $this->length))
            ->when($this->branchesList, fn($query) => $query->whereHas('branches', fn($query) => $query->whereIn('branch_of_medicines.id', $this->branchesList)))
            ->when($this->skillsList, fn($query) => $query->whereHas('skills', fn($query) => $query->whereIn('skills_for_questions.id', $this->skillsList)))
            ->when($this->specialitiesList, fn($query) => $query->whereHas('specialties', fn($query) => $query->whereIn('specialties.id', $this->specialitiesList)))
            ->limit(20)
            ->get();

        return $questions->isEmpty() ? null : $questions;
    }

    public function friendGame()
    {
        if ($this->questions?->count() < 2) {
            throw ValidationException::withMessages([
                'count' => 'The Count Should Be At Least 3 Questions',
            ]);
        }
        $this->validate([
            'difficulty' => ['nullable', 'string'],
            'difficulty.*' => [Rule::in(['easy', 'medium', 'hard', 'nerd'])],
            'length' => ['nullable', 'string'],
            'length.*' => [Rule::in(['short', 'medium', 'long'])],
            'branchesList' => ['nullable', 'array'],
            'branchesList.*' => ['exists:branch_of_medicines,id'],
            'skillsList' => ['nullable', 'array'],
            'skillsList.*' => ['exists:skills_for_questions,id'],
            'specialitiesList' => ['nullable', 'array'],
            'specialitiesList.*' => ['exists:specialties,id'],
            "references" => ["nullable" , "array"],
            "references.*" => ['exists:references,id'],
        ]);
        $gameService = app(GameService::class);
        $game = $gameService->friendGame(
            difficulty: $this->difficulty,
             length: $this->length,
              duration: $this->duration,
               sp: $this->specialitiesList,
                branches: $this->branchesList,
                 skills: $this->skillsList,
                  references :$this->references);


        return redirect()->route('friend.game.started', $game->challenge_token);
    }
};
?>

<div>
    <div class="card quiz-config-card">
        <div class="card-body">
            <div class="quiz-config-header">
                <h5 class="quiz-config-title">Configure Game</h5>
                <p class="quiz-config-subtitle">Set the parameters for your next game set.</p>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label for="branches" class="form-label">Branches For Medicine</label>
                    <div class="select2-primary" wire:ignore>
                        <select id="branches" class="select2 form-select branches" data-livewire-select2 multiple></select>
                    </div>
                    @error('branchesList')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-6 mb-4">
                    <label for="sp" class="form-label">Speciality</label>
                    <div class="select2-primary" wire:ignore>
                        <select id="sp" class="select2 form-select specialities" data-livewire-select2 multiple></select>
                    </div>
                    @error('specialitiesList')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label for="skills" class="form-label">Ideas From </label>
                    <div class="select2-primary" wire:ignore>
                        <select id="references" class="form-select select2" name="references" data-livewire-select2 multiple>
                            <option value=""></option>
                        </select>
                    </div>
                    @error('references')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-6 mb-4">
                    <label for="skills" class="form-label">Skills For Question</label>
                    <div class="select2-primary" wire:ignore>
                        <select id="skills" class="select2 form-select" data-livewire-select2 multiple></select>
                    </div>
                    @error('skillsList')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4" wire:ignore>
                    <label for="difficulty" class="form-label">Difficulty</label>
                    <div class="select2-primary">
                        <select id="difficulty" class="select2 form-select" data-livewire-select2>
                            <option value="">Select Difficulty</option>

                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                            <option value="nerd">Nerd</option>
                        </select>
                    </div>
                    @error('difficulty')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-md-6 mb-4" wire:ignore>
                    <label for="length" class="form-label">Length</label>
                    <div class="select2-primary">
                        <select id="length" class="select2 form-select" data-livewire-select2>
                            <option value="">Select Length</option>
                            <option value="short">Short</option>
                            <option value="medium">Medium</option>
                            <option value="long">Long</option>
                        </select>
                    </div>
                    @error('length')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-4" wire:ignore>
                    <label for="duration" class="form-label">Quiz Timer</label>
                    <div class="select2-primary">
                        <select id="duration" wire:model.live="duration" class="select2 form-select" data-livewire-select2>
                            <option value="">No duration</option>
                            <option value="{{ 1 * 60 }}">5 Minutes</option>
                            <option value="{{ 10 * 60 }}">10 Minutes</option>
                            <option value="{{ 15 * 60 }}">15 Minutes</option>
                            <option value="{{ 20 * 60 }}">20 Minutes</option>
                            <option value="{{ 30 * 60 }}">30 Minutes</option>
                            <option value="{{ 60 * 60 }}">60 Minutes</option>
                            <option value="{{ 90 * 60 }}">90 Minutes</option>
                            <option value="{{ 120 * 60 }}">120 Minutes</option>
                        </select>
                    </div>
                    @error('duration')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="quiz-config-footer">
                <p class="quiz-question-found">
                    Question Found {{ $this->questions?->count() == null ? 0 : $this->questions?->count() }}
                </p>
                @error('count')
                    <p class="text-danger my-3">{{ $message }}</p>
                @enderror

                <div class="quiz-config-actions">
                    <button class="btn btn-outline-info me-4" wire:click="friendGame">Friend Game</button>
                    {{-- <button class="btn btn-success" wire:click="submit">Start </button> --}}
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        const initializeConfigGameSelects = () => {
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
            select2.init('#difficulty', {
                placeholder: 'Select difficulty',
                multiple: false,
                onChange: (value) => $wire.set('difficulty', value),
            });
            select2.init('#length', {
                placeholder: 'Select length',
                multiple: false,
                onChange: (value) => $wire.set('length', value),
            });
            select2.init('#duration', {
                placeholder: 'Select quiz timer',
                multiple: false,
                onChange: (value) => $wire.set('duration', value),
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeConfigGameSelects, { once: true });
        } else {
            initializeConfigGameSelects();
        }
    </script>
@endscript
