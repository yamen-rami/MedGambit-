<x-user-layout>
    <x-slot:title>Quiz Result</x-slot:title>
    <div class="d-grid">
        <div class="card col-lg-8">
            <div class="my-5 ps-5">
                <h1 class="fs-5">Your Answered Questions</h1>
                <p class="fs-4">Score : <span>{{ $attempt->score }}</span></p>
                <p>
                    Quiz type :
                    <strong> {{ $quiz->type }} </strong>
                </p>
                @foreach ($answers as $answer)
                    <p>{{ $answer->question->content }}</p>
                    @foreach ($answer->question->options as $option)
                        @php
                            $correctId = $answer->question->correctAnswer->id;
                            $selectedId = $answer->option_id;
                        @endphp
                        <div
                            class="d-flex justify-content-between gap-2 align-items-center border
                                                        @if($correctId === $selectedId)
                                                          {{ $option->id === $correctId ? "border-success" : '' }}
                                                        @else
                                                          @if($option->id === $correctId)
                                                            border-success
                                                          @elseif($option->id === $selectedId)
                                                            border-danger
                                                          @endif
                                                        @endif

                                                            border-successd
                                                        px-4 py-5 rounded  mx-5 my-5"
                        >
                            <div class="">
                                <strong>{{ $option->name }} : </strong>{{ $option->content }}
                                {{-- @dump($option) --}}
                            </div>
                            <div>
                                <p
                                    class="text-primary mb-0 cursor-pointer"
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal{{ $loop->iteration }}"
                                    data-bs-whatever="@getbootstrap"
                                >
                                    See Explanation
                                </p>

                                <div
                                    class="modal fade modal-lg"
                                    id="exampleModal{{ $loop->iteration }}"
                                    tabindex="-1"
                                    aria-labelledby="exampleModalLabel"
                                    aria-hidden="true"
                                >
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="exampleModalLabel">Explanation</h1>
                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close"
                                                ></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="fw-bold text-white">{{ $option->explanation }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
    <div class="d-grid">
        <div class="col-lg-8 card my-5 px-4">
            <span class="fs-5 py-5">Un Answered Questions </span>
        </div>
    </div>
</x-user-layout>
{{-- <p class="text-white fs-4">
  {{ $question->content }}
</p>
<p>

  {{-- @foreach($question->options as $option)
<div class="d-flex justify-content-start gap-4 pt-5   border {{  $question->correctAnswer->id === $option->id ? "
  border-success" : 'border-primary' }} rounded px-3 align-items-center my-5 ">
                    <p class=" {{ $question->correctAnswer->id === $option->id ? "text-success" : 'text-danger' }}">
  <strong>{{ $option->name }} : </strong>{{ $option->content }}</p>
</div>
@endforeach --}}

{{-- </p> --}}
