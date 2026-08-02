<?php

use Livewire\Component;
use Livewire\Attributes\{On};
use App\Models\{Questions};
new class extends Component {
    //
    public $questionId = null;
    #[On("showQuestion")]
    public function show($id)
    {
        $this->questionId = $id;
    }
    public function getQuestionProperty()
    {
        return $this->questionId ? Questions::with("options")->findOrFail($this->questionId) : null;
    }
};
?>


<!-- Modal -->

<div wire:ignore.self class="modal modal-xl fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Question Info </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- <h1>Hello There {{ dd($this->question) }}</h1> --}}
                <p class="text-secondary fw-bold">
                <p class="fw-bold">Content</p>
                {{ $this->question?->content }}
                </p>
                <hr>
                <p>
                    {{ $this->question?->topic }}
                </p>
                <hr>
                <p>
                    {{ $this->question?->main_explanation }}
                </p>
                <hr>

                <p>
                    <span>Difficulty : </span>
                    <strong>
                        {{ $this->question?->difficulty }}
                    </strong>
                </p>
                <p>
                    <span>Length : </span>
                    <strong>
                        {{ $this->question?->length }}
                    </strong>
                </p>
                <p>
                    <span>Refernce : </span>
                    <strong>
                        {{ $this->question?->reference }}
                    </strong>
                </p>
                <div>
                    @foreach($this->question->options ?? [] as $option)
                        <div class="px-3 bg-inherit options  py-5 my-2 {{ $this->question->correctAnswer() === $option ? "success option-correct" : 'option'}}"
                            style="width:90%;">
                            <div class="d-flex align-items-center">
                                <span class="me-5">
                                    {{ $option->name }}
                                </span>
                                <span>
                                    {{ $option->content }}
                                </span>
                            </div>
                        </div>

                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary"><a
                        href="{{ route("questions.show", $this->question?->id ?? 0) }}" class="text-white">More Details
                    </a></button>
            </div>
        </div>
    </div>
</div>