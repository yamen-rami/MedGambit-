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
    $this->quiz = $quiz->loadMissing("questions.options");
    $this->attempt = QuizAttempt::where("user_id", auth()->id())->where("quiz_id", $this->quiz->id)->first();
  }

  public function hydrate()
  {
    $this->quiz->loadMissing("questions.options");
  }
  public function submit($optionId, $questionId)
  {

    // Get Every THings 
    $option = Option::with("question")->findOrFail($optionId);
    $question = Questions::with('options')->findOrFail($questionId);

    $this->answers[$question->id] = $option->id;
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
  #[Computed()]
  public function remainingSeconds()
  {
    if (!$this->attempt->finished_at) {
      return null;
    }
    return max(
      0,
      (int) now()->diffInSeconds($this->attempt->finished_at, false)
    );
  }
  public function updateCurrent($current)
  {
    $this->current = $current;
  }
  // public function submit(){
  // }
  public function finishQuiz()
  {
    $this->timerEnds();
  }
  public function timerEnds()
  {
    $questionsCount = $this->quiz->questions->count();
    $answers = $this->answers;
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
          @if($this->remainingSeconds != null)
            <div x-data="{
                                                  seconds: {{ $this->remainingSeconds ?? 0 }}, 
                                                  timer : null , 
                                                  get minutes(){
                                                  return Math.floor(this.seconds / 60)
                                                  },
                                                  get displaySeconds(){
                                                  return this.seconds % 60 
                                                  },
                                                  start(){
                                                  this.timer = setInterval(() => {
                                                  this.seconds-- ;
                                                  if(this.seconds <= 0 ){
                                                    clearInterval(this.timer); 
                                                    $wire.finishQuiz();
                                                  }
                                                  } ,1000)
                                                  }


                                                  }" x-init="start()">

              <button class="btn border border-info fs-5 my-4 text-white">
                <span x-text="minutes"></span>
                <span>:</span>
                <span x-text="String(displaySeconds).padStart(2 , '0')"></span>
              </button>
            </div>
          @endif
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
        <div class="options">
          <div class="option" wire:click='submit({{$option->id}}, {{ $question->id }})'>
            <div class="option-container">
              <p>{{ $option->content }}</p>
              <span>
                {{-- TODO Option Name --}}
                {{-- {{ $option->name }} --}}
                @if($loop->iteration === 1)
                  A
                @elseif ($loop->iteration === 2)
                  B
                @elseif($loop->iteration === 3)
                  C
                @elseif($loop->iteration === 4)
                  D
                @else
                  E
                @endif
              </span>
              <input class="input" type="radio" value="{{ $option->id }}" {{-- TODO Option Value --}}
                name="option-{{ $question->id }}" wire:model.live="answers.{{ $question->id }}">
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