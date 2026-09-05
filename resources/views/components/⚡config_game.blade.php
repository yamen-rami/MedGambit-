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

    public array $difficulty = [];

    public array $length = [];

    public $duration;

    public $branches;

    public $skills;

    public $specialities;

    #[Computed]
    public function questions()
    {
        if (empty($this->difficulty) && empty($this->length) && empty($this->branchesList) && empty($this->skillsList) && empty($this->specialitiesList)) {
            return null;
        }

        $questions = Questions::query()->when($this->difficulty, fn($query) => $query->whereIn('difficulty', $this->difficulty))->when($this->length, fn($query) => $query->whereIn('length', $this->length))->when($this->branchesList, fn($query) => $query->whereHas('branches', fn($query) => $query->whereIn('branch_of_medicines.id', $this->branchesList)))->when($this->skillsList, fn($query) => $query->whereHas('skills', fn($query) => $query->whereIn('skills_for_questions.id', $this->skillsList)))->when($this->specialitiesList, fn($query) => $query->whereHas('specialties', fn($query) => $query->whereIn('specialties.id', $this->specialitiesList)))->limit(20)->get();

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
            'difficulty' => ['nullable', 'array'],
            'difficulty.*' => [Rule::in(['easy', 'medium', 'hard', 'nerd'])],
            'length' => ['nullable', 'array'],
            'length.*' => [Rule::in(['short', 'medium', 'long'])],
            'branchesList' => ['nullable', 'array'],
            'branchesList.*' => ['exists:branch_of_medicines,id'],
            'skillsList' => ['nullable', 'array'],
            'skillsList.*' => ['exists:skills_for_questions,id'],
            'specialitiesList' => ['nullable', 'array'],
            'specialitiesList.*' => ['exists:specialties,id'],
        ]);
        $gameService = app(GameService::class);
        $game = $gameService->friendGame(
            difficulty: $this->difficulty,
            length:$this->length ,
            duration : $this->duration  ,
            sp : $this->specialitiesList,
            branches : $this->branchesList , 
            skills : $this->skillsList , 
        );
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
                        <select id="branches" class="select2 form-select branches" multiple></select>
                    </div>
                    @error('branchesList')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-6 mb-4">
                    <label for="specialities" class="form-label">Speciality</label>
                    <div class="select2-primary" wire:ignore>
                        <select id="specialities" class="select2 form-select specialities" multiple></select>
                    </div>
                    @error('specialitiesList')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>


            </div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <label for="skills" class="form-label">Skills For Question</label>
                    <div class="select2-primary" wire:ignore>
                        <select id="skills" class="select2 form-select" multiple></select>
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
                        <select id="difficulty" class="select2 form-select" multiple>
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
                        <select id="length" class="select2 form-select" multiple>
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
                        <select id="duration" wire:model.live="duration" class="select2 form-select">
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
                    Question Found
                    {{ $this->questions?->count()== null  ?  0 : $this->questions?->count()  }}
                </p>
                @error('count')
                    <p class="text-danger my-3">{{ $message }}</p>
                @enderror

                <div class="quiz-config-actions">
                    <button class="btn btn-outline-info   me-4" wire:click="friendGame">Friend Game </button>
                    {{-- <button class="btn btn-success" wire:click="submit">Start </button> --}}
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $(window).on('load', function() {
            if ($('#branches').hasClass('select2-hidden-accessible')) {
                $('#branches').select2('destroy');
            }

            $('#branches')
                .select2({
                    placeholder: 'Search for Branches ', // Your placeholder text

                    ajax: {
                        url: "{{ route('getBranches') }}",
                        type: 'GET',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map((branch) => ({
                                    id: branch.id,
                                    text: branch.name,
                                })),
                            };
                        },
                    },
                })
                .on('change', function() {
                    $wire.set('branchesList', $(this).val());
                });
            if ($('#specialities').hasClass('select2-hidden-accessible')) {
                $('#specialities').select2('destroy');
            }

            $('#specialities')
                .select2({
                    placeholder: 'Search for Specialities ',
                    ajax: {
                        url: "{{ route('getSpeciality') }}",
                        type: 'GET',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map((s) => ({
                                    id: s.id,
                                    text: s.name,
                                })),
                            };
                        },
                    },
                })
                .on('change', function() {
                    $wire.set('specialitiesList', $(this).val());
                });
            // ! Skills
            if ($('#skills').hasClass('select2-hidden-accessible')) {
                $('#skills').select2('destroy');
            }

            $('#skills')
                .select2({
                    placeholder: 'Search for Skills ',
                    ajax: {
                        url: "{{ route('getSkills') }}",
                        type: 'GET',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map((skill) => ({
                                    id: skill.id,
                                    text: skill.name,
                                })),
                            };
                        },
                    },
                })
                .on('change', function() {
                    $wire.set('skillsList', $(this).val());
                });
        });
        $('#difficulty')
            .select2()
            .on('change', function() {
                $wire.set('difficulty', $(this).val());
            });
        $('#length')
            .select2()
            .on('change', function() {
                $wire.set('length', $(this).val());
            });
        $('#duration')
            .select2()
            .on('change', function() {
                $wire.set('duration', $(this).val());
            });
    </script>
@endscript
