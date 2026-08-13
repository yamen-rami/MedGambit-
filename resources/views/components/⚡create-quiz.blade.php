<?php

use App\Models\Questions;
use App\Models\Quiz;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    /*
        Click added to results
        make the color green how by checking if there value of that record the color green

    */
    public $search = '';

    public $branchesList = [];

    public $skillsList = [];

    public $specialityList = [];

    protected string $paginationTheme = 'bootstrap';

    public $specialties = [];

    public $branches = [];

    public $skills = [];

    public $length = [];

    public $difficulty = [];

    public int $questionNumber = 3;

    public string $quizTopic = '';

    public string $quizDifficulty = '';

    public string $quizLength = '';

    public string $quizContent = '';

    public string $quizName = '';

    public $sort = 'desc';

    public $results = [];

    public function mount($specialityList, $skillsList, $branchesList)
    {
        $this->specialityList = $specialityList;
        $this->skillsList = $skillsList;
        $this->branchesList = $branchesList;
    }

    #[Computed()]
    public function questions()
    {
        $query = Questions::query();
        if (! empty($this->search)) {
            $query->where(
                function ($q) {
                    $q->where('content', 'LIKE', "%{$this->search}%")->OrWhere('topic', 'LIKE', "%{$this->search}%");
                }
            );
        }
        if (! empty($this->length)) {
            $query->whereIn('length', $this->length);
        }
        if (! empty($this->difficulty)) {
            $query->whereIn('difficulty', $this->difficulty);

        }
        if (! empty($this->specialties)) {
            // $query->whereRelation("specialties", "specialties.id", $this->specialties);
            $query->whereHas('specialties', function ($q) {
                $q->whereIn('specialties.id', $this->specialties);
            });

        }
        if (! empty($this->branches)) {
            // $query->whereRelation("branches", "branch_of_medicines.id", $this->branches);
            $query->whereHas('branches', function ($q) {
                $q->whereIn('branch_of_medicines.id', $this->branches);
            });
        }
        if (! empty($this->skills)) {
            // $query->whereRelation("skills", "skills_for_questions.id", $this->skills);
            $query->whereHas('skills', function ($q) {
                $q->whereIn('skills_for_questions.id', $this->skills);
            });
        }
        $questions = $query->orderBy('id', $this->sort)->paginate(10);

        return $questions;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedLength()
    {

        $this->resetPage();
    }

    public function updatedDifficulty()
    {
        $this->resetPage();
    }

    public function add($questionId)
    {
        // count is = 20 then stop
        if (count($this->results) >= $this->questionNumber) {
            return;
        }
        if (array_key_exists($questionId, $this->results)) {
            return;
        }
        $this->results[$questionId] = true;
    }

    public function remove($questionId)
    {
        unset($this->results[$questionId]);
    }

    public function clear()
    {
        // reset everything
        $this->search = '';
        $this->length = [];
        $this->difficulty = [];
        $this->specialties = []; // Reset these too
        $this->branches = [];
        $this->skills = [];
        $this->resetPage();
    }

    public function save()
    {
        $data = $this->validate([
            'questionNumber' => ['required', 'integer', 'min:3', 'max:20'],
            'quizName' => ['required', 'string', 'min:3'],
            'quizTopic' => ['required', 'string', 'min:3'],
            'quizDifficulty' => ['required', Rule::in(['easy', 'medium', 'hard', 'nerd'])],
            'quizLength' => ['required', Rule::in(['short', 'medium', 'long'])],
            'results' => ['required', 'array'],
        ]);
        $quiz = Quiz::create([
            'name' => $data['quizName'],
            'questions_number' => $data['questionNumber'],
            'type' => 'admin',
            'topic' => $data['quizTopic'],
            'difficulty' => $data['quizDifficulty'],
            'length' => $data['quizLength'],
        ]);
        $questions = array_keys($this->results);
        // faster than foreach
        $quiz->questions()->attach($questions);

        flash()->success('Quiz Has Created Successfully');

        return redirect()->route('quizez.index');
    }

    public function updatedSpecialties()
    {
        $this->resetPage();
    }

    public function updatedBranches()
    {
        $this->resetPage();
    }

    public function updatedSkills()
    {
        $this->resetPage();
    }
};
?>

<div class="my-2">
    <div class="col-lg-12 card my-3">
        <div class="row">
            <form wire:submit="save">
                <div class="col-md-6 col-lg-6">
                    <div class="card">
                        <h5 class="card-header">Create Quiz</h5>
                        <div class="card-body">
                            <x-textarea wire:model="quizName" label="Quiz Name" name="quizName"> </x-textarea>
                            <x-textarea wire:model="quizTopic" label="Topic" name="quizTopic"></x-textarea>
                            <x-forms.input
                                wire:model="questionNumber"
                                z
                                label="Questions Number"
                                type="number"
                                max="20"
                                min="2"
                                name="questionNumber"
                            ></x-forms.input>
                            <div style="width: fit-content">
                                <p class="border-bottom border-primary" style="font-size: 13px; width: fit">
                                    *Must Be At least
                                    <span class="text-info">3</span> Questions, Max <span class="text-info">20</span>
                                    Questions
                                </p>
                            </div>

                            {{-- ? Difficulty --}}
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Difficulty </label>
                                <select class="form-select" wire:model="quizDifficulty" name="quizDifficulty">
                                    <option value="easy" selected>Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                    <option value="nerd">Nerd</option>
                                </select>
                                @error('quizDifficulty')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- Length --}}
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Length</label>
                                <select class="form-select" wire:model="quizLength" name="quizLength">
                                    <option value="short" selected>Short</option>
                                    <option value="medium">Medium</option>
                                    <option value="long">Long</option>
                                </select>
                                @error('quizLength')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="py-2">
                                <label for="exampleFormControlInput1" class="form-label">Difficulty</label>
                                <div class="d-flex gap-2">
                                    <div class="form-check">
                                        <label>Easy</label>
                                        <input
                                            wire:model.live="difficulty"
                                            value="easy"
                                            class="form-check-input"
                                            type="checkbox"
                                        />
                                    </div>
                                    <div class="form-check">
                                        <label>Medium</label>
                                        <input
                                            wire:model.live="difficulty"
                                            value="medium"
                                            class="form-check-input"
                                            type="checkbox"
                                        />
                                    </div>
                                    <div class="form-check">
                                        <label>Hard</label>
                                        <input
                                            wire:model.live="difficulty"
                                            value="hard"
                                            class="form-check-input"
                                            type="checkbox"
                                        />
                                    </div>
                                    <div class="form-check">
                                        <label>Nerd</label>
                                        <input
                                            wire:model.live="difficulty"
                                            value="nerd"
                                            class="form-check-input"
                                            type="checkbox"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="py-2">
                                <label for="exampleFormControlInput1" class="form-label">Length</label>
                                <div class="d-flex gap-2">
                                    <div class="form-check">
                                        <label>Short</label>
                                        <input
                                            wire:model.live="length"
                                            value="short"
                                            class="form-check-input"
                                            type="checkbox"
                                        />
                                    </div>
                                    <div class="form-check">
                                        <label>Medium</label>
                                        <input
                                            wire:model.live="length"
                                            value="medium"
                                            class="form-check-input"
                                            type="checkbox"
                                        />
                                    </div>
                                    <div class="form-check">
                                        <label>Long</label>
                                        <input
                                            wire:model.live="length"
                                            value="long"
                                            class="form-check-input"
                                            type="checkbox"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="py-2">
                                <label for="exampleFormControlInput1" class="form-label">Branch Of Medicine</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($this->branchesList as $branch)
                                        <div class="form-check">
                                            <label>{{ $branch->name }}</label>
                                            <input
                                                wire:model.live="branches"
                                                value="{{ $branch->id }}"
                                                class="form-check-input"
                                                type="checkbox"
                                            />
                                        </div>
                                    @endforeach
                                    <hr />
                                </div>
                            </div>
                            <div class="py-2">
                                <label for="exampleFormControlInput1" class="form-label">Skills For Questions</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($this->skillsList as $skill)
                                        <div class="form-check">
                                            <label>{{ $skill->name }}</label>
                                            <input
                                                wire:model.live="skills"
                                                value="{{ $skill->id }}"
                                                class="form-check-input"
                                                type="checkbox"
                                            />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="py-2">
                                <label for="exampleFormControlInput1" class="form-label">Speciality</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($this->specialityList as $specality)
                                        <div class="form-check">
                                            <label>{{ $specality->name }}</label>
                                            <input
                                                wire:model.live="specialties"
                                                value="{{ $specality->id }}"
                                                class="form-check-input"
                                                type="checkbox"
                                            />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @if (! empty($search) || ! empty($length) || ! empty($difficulty) || ! empty($specialties) || ! empty($branches) || ! empty($skills))
                                <div class="alert alert-success fs-5">Filters Applied</div>
                            @endif
                            <div class="text-end">
                                <button class="btn btn-primary">Create Quiz</button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <div class="card">
        <div style="overflow-x: hidden" class="card-datatable table-responsive pt-0">
            <div class="d-grid">
                <div class="row align-items-center py-4">
                    <div class="col-lg-4 text-center">
                        <div class="dropdown">
                            <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                                <div class="">
                                    <img src="{{ asset("assets/images/filter.svg") }}" alt="" />
                                </div>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item">
                                    <div class="text-center">
                                        <button wire:click="sorting" class="btn text-start" type="button">
                                            <img
                                                src="{{ asset($this->sort === "desc" ? "assets/images/arrow_down.svg" : "assets/images/arrow_top.svg") }}"
                                                alt="Arrows "
                                            />
                                        </button>
                                    </div>
                                </a>
                                <a class="dropdown-item">
                                    <div class="d-flex align-items-center length-parent text-start">
                                        <div class="main-text mt-1">
                                            <span class="length">Length</span>
                                        </div>
                                        <div class="length-div">
                                            <div>
                                                <button
                                                    type="button"
                                                    wire:click='filterLenght("short")'
                                                    class="btn btn-outline-info"
                                                >
                                                    Short
                                                </button>
                                            </div>
                                            <div>
                                                <button
                                                    type="button"
                                                    wire:click='filterLenght("medium")'
                                                    class="btn btn-outline-info"
                                                >
                                                    Medium
                                                </button>
                                            </div>
                                            <div>
                                                <button
                                                    type="button"
                                                    wire:click='filterLenght("long")'
                                                    class="btn btn-outline-info"
                                                >
                                                    long
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item">
                                    <div class="hover">
                                        <div class="d-flex justify-content-start align-items-center gap-2">
                                            <div class="main-text mt-1">
                                                <span class="length">Difficulty</span>
                                            </div>
                                            <div class="difficulty">
                                                <div>
                                                    <button
                                                        type="button"
                                                        wire:click='filterDif("easy")'
                                                        class="btn btn-outline-danger"
                                                    >
                                                        Easy
                                                    </button>
                                                </div>
                                                <div>
                                                    <button
                                                        type="button"
                                                        wire:click='filterDif("medium")'
                                                        class="btn btn-outline-danger"
                                                    >
                                                        Medium
                                                    </button>
                                                </div>
                                                <div>
                                                    <button
                                                        type="button"
                                                        wire:click='filterDif("hard")'
                                                        class="btn btn-outline-danger"
                                                    >
                                                        Hard
                                                    </button>
                                                </div>
                                                <div>
                                                    <button
                                                        type="button"
                                                        wire:click='filterDif("nerd")'
                                                        class="btn btn-outline-danger"
                                                    >
                                                        Nerd
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="d-flex gap-3">
                            {{-- TODO Clear Search --}}
                            <input
                                type="text"
                                wire:model.live.debounce="search"
                                name="search"
                                class="form-control ps-5"
                                placeholder="Search quizez"
                            />
                            <button type="button" wire:click="clear" class="btn btn-danger">Clear</button>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center"></div>
                </div>
            </div>
            </form>
            <table class="datatables-basic table">
                @error('results')
                    <h1 class="text-danger fs-6 text-center">{{ $message }}</h1>
                @enderror
                <thead>
                    <tr class="ps-3 pe-4">
                        <th>id</th>
                        <th>Image</th>
                        <th>Content</th>
                        <th>Topic</th>
                        <th>Solved</th>
                        <th>difficulty</th>
                        <th>Length</th>
                        <th>Elo Correct</th>
                        <th>Elo InCorrect</th>
                        <th>Reference</th>
                        <th>Add</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->questions as $question)
                        <tr>
                            <th>{{ $question->id }}</th>
                            <td>
                                <ul class="list-unstyled avatar-group d-flex align-items-center m-0">
                                    <li
                                        data-bs-toggle="tooltip"
                                        data-popup="tooltip-custom"
                                        data-bs-placement="top"
                                        class="avatar avatar-xs pull-up"
                                        title="{{ $question->content }}"
                                    >
                                        <img src="{{ asset($question->image) }}" alt="Avatar" class="rounded-circle" />
                                    </li>
                                </ul>
                            </td>
                            <th>{{ Str::limit($question->content, 10) }}</th>
                            <th>{{ Str::limit($question->topic, 10) }}</th>
                            <th>{{ $question->solved }}</th>
                            <th>{{ $question->difficulty }}</th>
                            <th>{{ $question->length }}</th>
                            <th>{{ $question->elo_correct }}</th>
                            <th>{{ $question->elo_incorrect }}</th>
                            <th>{{ Str::limit($question->reference, 10) }}</th>
                            <td
                                wire:click='add({{ $question->id }})'
                                class="{{ array_key_exists($question->id, $this->results) ? "text-success" : "text-white" }}"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                    />
                                </svg>
                            </td>
                            <td wire:click='remove({{ $question->id }})'>
                                <img src="{{ asset('assets/images/remove.svg') }}" alt="" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="my-3">{{ $this->questions->links() }}</div>
            </form>

            <div class="content-backdrop fade"></div>
        </div>
    </div>
