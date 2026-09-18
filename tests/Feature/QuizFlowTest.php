<?php

use App\Models\Answers;
use App\Models\Questions;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function quizForFlow(array $attributes = []): Quiz
{
    return Quiz::create(array_merge([
        'name' => 'Flow quiz',
        'topic' => 'Clinical medicine',
        'difficulty' => 'easy',
        'length' => 'short',
        'type' => 'admin',
        'questions_number' => 1,
    ], $attributes));
}

test('a user can open the start quiz page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('start.quiz'))
        ->assertOk()
        ->assertSee('Configure Exam');
});

test('a user can answer and navigate the start quiz component', function () {
    $user = User::factory()->create();
    $quiz = quizForFlow(['type' => 'detected']);
    $firstQuestion = Questions::factory()->create([
        'name' => 'First question',
        'content' => '<strong>Rich question content</strong>',
        'topic' => '<em>Rich topic</em>',
        'main_explanation' => '<p><strong>Rich main explanation</strong></p>',
        'high_yield' => '<p><em>Rich high yield</em></p>',
        'elo_correct' => '12',
        'elo_incorrect' => '10',
    ]);
    $secondQuestion = Questions::factory()->create(['name' => 'Second question']);
    $quiz->questions()->attach([$firstQuestion->id, $secondQuestion->id]);
    $attempt = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'status' => 'pending',
        'score' => 0,
        'current' => 1,
        'finished_at' => now()->addMinutes(20),
    ]);
    $option = $firstQuestion->options()->firstOrFail();

    Livewire::actingAs($user)
        ->test('start-quiz', ['quiz' => $quiz])
        ->assertSee('First question')
        ->assertSeeHtml('<strong>Rich question content</strong>')
        ->assertSeeHtml('<em>Rich topic</em>')
        ->assertSee('+12 ELO')
        ->assertSee('-10 ELO')
        ->assertSee('Timer')
        ->call('submit', $option->id, $firstQuestion->id)
        ->assertSet("answers.{$firstQuestion->id}", $option->id)
        ->call('next')
        ->assertSet('current', 2)
        ->assertSee('Second question')
        ->assertSee('Submit quiz');

    expect($attempt->fresh()->current)->toBe(2);
    $this->assertDatabaseHas('answers', [
        'quiz_attempt_id' => $attempt->id,
        'question_id' => $firstQuestion->id,
        'option_id' => $option->id,
        'status' => 'answered',
    ]);
});

test('a user can review a finished start quiz attempt without restarting it', function () {
    $user = User::factory()->create();
    $quiz = quizForFlow(['type' => 'detected']);
    $question = Questions::factory()->create();
    $quiz->questions()->attach($question);
    $attempt = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'status' => 'finished',
        'score' => 1,
        'current' => 1,
        'finished_at' => now()->subMinute(),
    ]);
    $option = $question->correctAnswer()->firstOrFail();
    Answers::create([
        'quiz_attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'option_id' => $option->id,
        'is_correct' => true,
        'status' => 'answered',
    ]);

    Livewire::actingAs($user)
        ->test('start-quiz', ['quiz' => $quiz])
        ->assertSet('current', 1)
        ->assertSet('answers.' . $question->id, $option->id)
        ->assertSet('remainingSeconds', null)
        ->call('submit', $option->id, $question->id);

    expect($attempt->fresh()->status)->toBe('finished');
});

test('a user can open a learning quiz and answer a question', function () {
    $user = User::factory()->create();
    $quiz = quizForFlow(['type' => 'learning']);
    $question = Questions::factory()->create();
    $quiz->questions()->attach($question);
    $attempt = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'status' => 'pending',
        'score' => 0,
        'current' => 1,
    ]);
    $correctOption = $question->correctAnswer()->firstOrFail();

    Livewire::actingAs($user)
        ->test('learning-quiz', ['quiz' => $quiz])
        ->assertSet('current', 1)
        ->assertSeeHtml('<button class="answer-choice" type="button"')
        ->call('submit', $correctOption->id, $question->id)
        ->assertSet('correctCount', 1);

    $this->assertDatabaseHas('answers', [
        'quiz_attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'option_id' => $correctOption->id,
        'is_correct' => true,
    ]);
});

test('a finished quiz result belongs to the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $quiz = quizForFlow();
    $question = Questions::factory()->create();
    $quiz->questions()->attach($question);
    $option = $question->correctAnswer()->firstOrFail();

    $attempt = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'status' => 'finished',
        'score' => 1,
    ]);
    Answers::create([
        'quiz_attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'option_id' => $option->id,
        'is_correct' => true,
        'status' => 'answered',
    ]);

    $this->actingAs($user)
        ->get(route('quizResult', $quiz))
        ->assertOk()
        ->assertSee('Quiz Type : admin')
        ->assertSee('Final Score');

    $this->actingAs($otherUser)
        ->get(route('quizResult', $quiz))
        ->assertForbidden();
});
