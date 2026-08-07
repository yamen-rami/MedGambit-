<?php


use Livewire\Component;
use App\Services\QuizService;
use Livewire\Attributes\{On, Computed, Session};
use App\Models\{Questions, Option, QuizAttempt};
new class extends Component {
  public $quiz;

  #[Session]
  public $current = 1;
  #[Session]
  public array $answers = [];

  public $attempt;
  public function mount($quiz)
  {
    // Start A New Quiz Means a New Id which mean that if the 
    $this->quiz = $quiz->loadMissing(["questions.options", "questions.correctAnswer"]);
    $this->attempt = QuizAttempt::where("user_id", auth()->id())->where("quiz_id", $this->quiz->id)->first();
  }

  public function hydrate()
  {
    $this->quiz->loadMissing(["questions.options", "questions.correctAnswer"]);
  }
  public function submit($optionId, $questionId)
  {
    if (!array_key_exists($questionId, $this->answers)) {
      $this->answers[$questionId] = $optionId;
    }


  }
  public function editCurrent($current)
  {
    $this->current = $current;
  }
  public function next()
  {
    if ($this->current < $this->quiz->questions->count()) {
      $this->current++;
    }
  }
  public function previous()
  {
    if ($this->current > 1) {
      $this->current--;
    }
  }

  public function updateCurrent($current)
  {
    $this->current = $current;
  }

  public function submitAttempt()
  {
    $questionsCount = $this->quiz->questions->count();
    $answers = $this->answers;
    $this->validate([
      "answers" => ["required", "array", "min:$questionsCount"]
    ]);
    $quizService = app(QuizService::class);

    $attempt = $quizService->updateAttempt(auth()->id(), $this->quiz->id, $answers);
    $this->reset("current", "answers");

    session()->forget([
      'current',
      'answers',
      "array",
    ]);
    return redirect()->route("quizResult", $this->quiz);
  }
};
    ?>
<div>
  @foreach($quiz->questions as $question)
    <div id="question-{{ $this->current }}" class="{{ $loop->iteration == $current ? "showQuiz" : "hide" }}">
      <div class="main-title">
        <div class="d-flex justify-content-between align-items-center">
          <h1>Question {{ $loop->iteration }} from {{ $quiz->questions->count() }} </h1>
        </div>
        <p class="question-content">
          {{-- TODO Question --}}
          {{ $question->content }}
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Iusto tempore aut itaque rerum, molestias
          odio. Aspernatur perferendis sint autem iste at ab blanditiis nemo deleniti delectus repellendus, ipsa
          ipsam amet veniam tenetur pariatur esse. Amet mollitia officia quae optio dolores.
        </p>
      </div>
      <hr>
      @foreach($question->options as $option)
        <div wire:click='submit({{ $option->id }} , {{ $question->id }})'
          class="option border  border-secondary rounded px-4 my-2"
          x-data="{show : false , correct : {{ $option->id }} == {{ $question->correctAnswer->id }} }" @click="show = !show"
          :class="{
                                        'border-success': show && correct,
                                        'border-danger': show && ! correct
                                    }">
          <div class="">
            <div class="d-flex  align-items-center gap-4  text-white py-3 rounded">
              <span class="text-white fs-6  "> @if($loop->iteration === 1)
                A
              @elseif ($loop->iteration === 2)
                  B
                @elseif($loop->iteration === 3)
                  C
                @elseif($loop->iteration === 4)
                  D
                @else
                  E
                @endif</span>
              <input class="input" type="radio" value="{{ $option->id }}" {{-- TODO Option Value --}}
                name="option-{{ $question->id }}" wire:model.live="answers.{{ $question->id }}">
              <p class="mb-0  option-content">{{ $option->content }}</p>
            </div>
            <div x-show="show" x-cloak x-transition.100ms>
              <div class="row align-items-center">
                <div class="col-lg-1">

                  <div style="width: 50px ; height: 50px ;">
                    @if($option->id == $question->correctAnswer->id)
                      <svg class="text-success" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        strokeWidth={1.5} stroke="currentColor" className="size-6">
                        <path strokeLinecap="round" strokeLinejoin="round"
                          d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                      </svg>
                    @else
                      <svg class="text-danger" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                      </svg>

                    @endif

                  </div>
                </div>
                <p class="mb-0 text-white fs-6 fw-bold pt-3 pb-4 col-lg-10 ">
                  {{ $option->explanation }}
                </p>
              </div>

            </div>
          </div>
        </div>


      @endforeach

      <hr>
      <div class="next-previous">
        <button class="previous" {{ $loop->first ? "disabled" : '' }} wire:click='previous'>Previous</button>

        @if(!$loop->last)
          <button class="next" {{ $loop->last ? "disabled" : '' }} wire:click='next'>Next</button>
        @else
          <button class="next" wire:click='submitAttempt'>Submit</button>
        @endif
      </div>
      <hr>
      <div class=" paginate">
        <p>Jump To Question</p>
        <div class="paginate-numbers">
          @foreach($quiz->questions as $question)
            <button
              class="
                                                                                                                                                                                                                                                                                                                                     @if(isset($this->answers[$question->id]))
                                                                                                                                                                                                                                                                                                                                      question_success
                                                                                                                                                                                                                                                                                                                                    @elseif ($this->current === $loop->iteration)
                                                                                                                                                                                                                                                                                                                                       question_primary
                                                                                                                                                                                                                                                                                                                                    @else
                                                                                                                                                                                                                                                                                                                                      question_number
                                                                                                                                                                                                                                                                                                                                    @endif
                                                                                                                                                                                                                                                                                                                                      "
              wire:click='updateCurrent({{ $loop->iteration }})'>{{ $loop->iteration }}</button>

          @endforeach
        </div>
      </div>
    </div>
  @endforeach
  @error("answers")
    <h1 class="my-2 text-center text-danger fs-5">Please Add Answers Left Questions Answers =
      {{ $this->quiz->questions->count() - count($this->answers) }}
    </h1>
  @enderror
  <script>

  </script>
</div>