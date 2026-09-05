<?php

use App\Models\Questions;
use App\Services\QuizService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public array $branchesList = [];

    public array $references = [];

    public array $specialitiesList = [];

    public array $skillsList = [];

    public string $difficulty = '';

    public string $length = '';

    public $duration;

    public $branches;

    public $skills;

    public $specialities;

    #[Computed]
    public function questions()
    {
        if (empty($this->difficulty) && empty($this->length) && empty($this->branchesList) && empty($this->skillsList) && empty($this->specialitiesList) && empty($this->references)) {
            return null;
        }
        $user = auth()->user();
        $userPlayedQuestion = $user->playedQuestions->pluck('id');
        $query = Questions::query()
            ->when($userPlayedQuestion->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $userPlayedQuestion))

            ->when($this->difficulty, fn ($query) => $query->where('difficulty', $this->difficulty))
            ->when($this->length, fn ($query) => $query->where('length', $this->length))
            ->when($this->branchesList, function ($query) {
                $query->whereHas('branches', function ($query) {
                    $query->whereIn('branch_of_medicines.id', $this->branchesList);
                });
            })
            ->when($this->references, function ($query) {
                $query->whereIn('reference_id', $this->references);
            })
            ->when($this->skillsList, function ($query) {
                $query->whereHas('skills', function ($query) {
                    $query->whereIn('skills_for_questions.id', $this->skillsList);
                });
            })
            ->when($this->specialitiesList, function ($query) {
                $query->whereHas('specialties', function ($query) {
                    $query->whereIn('specialties.id', $this->specialitiesList);
                });
            });

        $questions = $query->limit(20)->get();
        if ($questions->count() < 20) {
            $remaining = 20 - $questions->count();

            $fallback = Questions::query()
                ->when($this->difficulty, fn ($query) => $query->where('difficulty', $this->difficulty))
                ->when($this->length, fn ($query) => $query->where('length', $this->length))
                ->whereIn('id', $userPlayedQuestion)
                ->whereNotIn('id', $questions->pluck('id'))
                ->limit($remaining)
                ->get();
            $questions = $questions->merge($fallback);
        }

        return $questions;
    }

    public function submit()
    {
        // Of the Current User
        if (! $this->questions) {
            throw ValidationException::withMessages([
                'count' => 'The Count Should Be At Least 3 Questions',
            ]);
        }
        if ($this->questions?->count() < 2) {
            throw ValidationException::withMessages([
                'count' => 'The Count Should Be At Least 3 Questions',
            ]);
        }
        $validate = $this->validate([
            'difficulty' => ['nullable', 'string'],
            'length' => ['nullable', 'string'],
            'branchesList' => ['nullable', 'array'],
            'branchesList.*' => ['exists:branch_of_medicines,id'],
            'skillsList' => ['nullable', 'array'],
            'skillsList.*' => ['exists:skills_for_questions,id'],
            'specialitiesList' => ['nullable', 'array'],
            'specialitiesList.*' => ['exists:specialties,id'],
            'references' => ['nullable', 'array'],
            'references.*' => ['exists:references,id'],
        ]);
        // dd($validate);
        $duration = (int) $this->duration;
        $quizService = app(QuizService::class);
        $quiz = $quizService->detectedQuiz(questions: $this->questions, length: $this->length ? $this->length : 'medium', difficulty: $this->difficulty ? $this->difficulty : 'medium', duration: $this->duration, count: $this->questions->count());

        return redirect()->route('start.detecated.quiz', $quiz);
    }

    public function learningQuiz()
    {
        if (! $this->questions) {
            throw ValidationException::withMessages([
                'count' => 'The Count Should Be At Least 3 Questions',
            ]);
        }
        if ($this->questions?->count() < 2) {
            throw ValidationException::withMessages([
                'count' => 'The Count Should Be At Least 3 Questions',
            ]);
        }
        $this->validate([
            'difficulty' => ['nullable', 'string'],
            'length' => ['nullable', 'string'],
            'branchesList' => ['nullable', 'array'],
            'branchesList.*' => ['exists:branch_of_medicines,id'],
            'skillsList' => ['nullable', 'array'],
            'skillsList.*' => ['exists:skills_for_questions,id'],
            'specialitiesList' => ['nullable', 'array'],
            'specialitiesList.*' => ['exists:specialties,id'],
            'references' => ['nullable', 'array'],
            'references.*' => ['exists:references,id'],
        ]);
        $quizService = app(QuizService::class);
        $quiz = $quizService->learningQuiz(
            questions: $this->questions,
            length: $this->length ? $this->length : 'short',
            count: $this->questions->count(),
            difficulty: $this->difficulty ?? 'hard'
        );

        return redirect()->route('start.learning.quiz', $quiz);
    }
};
?>

<div>
    <div class="card quiz-config-card">
        <div class="card-body">
            <div class="quiz-config-header">
                <h5 class="quiz-config-title">Configure Exam</h5>
                <p class="quiz-config-subtitle">Set the parameters for your next question set.</p>
            </div>

            <div class="row">
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <label for="skills" class="form-label">Ideas From </label>
                    <div class="select2-primary" wire:ignore>
                        <select id="references" class="form-select select2" name="references" multiple>
                            <option value=""></option>
                        </select>
                    </div>
                    @error('references')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-6 mb-4" wire:ignore>
                    <label for="difficulty" class="form-label">Difficulty</label>
                    <div class="select2-primary">
                        <select id="difficulty" class="select2 form-select">
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
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label for="skills" class="form-label">Skills For Question</label>
                    <div class="select2-primary" wire:ignore>
                        <select id="skills" class="select2 form-select" multiple></select>
                    </div>
                    @error('skillsList')
                        <p class="text-danger py-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-md-6 mb-4" wire:ignore>
                    <label for="length" class="form-label">Length</label>
                    <div class="select2-primary">
                        <select id="length" class="select2 form-select">
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
                    Question Found {{ $this->questions?->count() != 0 ? $this->questions?->count() : 0 }}
                </p>
                @error('count')
                    <p class="text-danger my-3">{{ $message }}</p>
                @enderror

                <div class="quiz-config-actions">
                    <button class="btn btn-outline-warning me-4" wire:click="learningQuiz">Start Learning Exam</button>
                    <button class="btn btn-success" wire:click="submit">Start Exam</button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $(window).on('load', function () {
            if ($('#branches').hasClass('select2-hidden-accessible')) {
                $('#branches').select2('destroy');
            }
            $('#references')
                .select2({
                    placeholder: 'Search for References ',
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
                        data: function (params) {
                            return {
                                search: params.term,
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map((s) => ({
                                    id: s.id,
                                    text: s.name,
                                })),
                            };
                        },
                    },
                })
                .on('change', function () {
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
                    $wire.set('skillsList', $(this).val());
                });
        });

        $('#difficulty')
            .select2({
                placeholder: 'Select Difficulty',
            })
            .on('change', function () {
                $wire.set('difficulty', $(this).val());
            });
        $('#length')
            .select2()
            .on('change', function () {
                $wire.set('length', $(this).val());
            });
        $('#duration')
            .select2()
            .on('change', function () {
                $wire.set('duration', $(this).val());
            });
    </script>
@endscript
