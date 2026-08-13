<?php

use App\Models\Quiz;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    /*
        1- Quiz Varialbe
        2- Quiz Showing
        3- Quiz Questions something like paginate
    */
    public ?Quiz $quiz;

    public function mount($quiz)
    {
        $this->quiz = Quiz::with(['questions', 'questions.options'])->findOrFail($quiz->id);
        // The Query I want something to pass and then just
    }

    public function open($id)
    {
        $this->dispatch('showQuestion', id: $id);
    }

    #[Computed()]
    public function questions()
    {
        $this->quiz->loadMissing('questions.options');

        return $this->quiz->questions;
    }
};
?>

<div>
    <div class="card">
        <div class="col-lg-9 px-4 text-start">
            <h1 class="fw-1 fs-4 py-4">Show Quiz</h1>
            <p class="py-3 text-white">{{ $this->quiz->name }}</p>
            <hr />
            <p class="py-3 text-white">{{ $this->quiz->topic }}</p>
            <hr />
            <p class="py-2 text-white">{{ $this->quiz->difficulty }}</p>
            <hr />
            <p class="py-3 text-white">{{ $this->quiz->length }}</p>
        </div>
    </div>
    <div class="py-3">
        <h1 class="fw-1 fs-4 py-4">Quiz Questions</h1>

        <div style="margin: 0 auto" class="d-grid col-lg-8 card quiz px-4 py-4">
            @foreach ($this->questions as $question)
                <div id="question-{{ $loop->iteration }}" class="{{ $loop->first ? 'show' : "hide" }}">
                    <p class="fw-bold px-5 py-4 text-end">
                        <a>
                            <button
                                type="button"
                                wire:click='open({{ $question->id }})'
                                class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#staticBackdrop"
                            >
                                Show Question
                            </button>
                        </a>
                    </p>
                    {{--! Show Modal --}}

                    {{-- ! End Modal --}}
                    <div class="d-flex align-items-center justify-content-between">
                        <p class="fw-bold py-4 text-start">{{ $question->content }}</p>
                    </div>
                    @foreach ($question->options as $option)
                        <div
                            class="option border border-primary rounded-5 my-2 py-3 d-flex   {{ $question->correctAnswer() === $option ? "text-white  border-success option-correct" : '' }}  "
                        >
                            <p class="ps-5">
                            <div class="d-flex align-items-center py-3">
                                <span class="d-inline ms-5"> {{ $option->name }} </span>
                                <span class="px-3"> {{ $option->content }} </span>
                            </div>
                            </p>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between">
                        <button
                            {{ $loop->first ? "disabled" : '' }}
                            onclick="before({{ $loop->iteration }})"
                            class="btn btn-outline-info my-2 text-end"
                        >
                            Previous
                        </button>
                        <button
                            {{ $loop->last ? "disabled" : '' }}
                            onclick="show({{ $loop->iteration }})"
                            class="btn btn-outline-primary my-2 text-end"
                        >
                            Next
                        </button>
                    </div>
                </div>

            @endforeach
        </div>
    </div>
    <livewire:show-question></livewire:show-question>
</div>
