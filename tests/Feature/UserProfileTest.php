<?php

use App\Models\{Questions, Quiz, QuizAttempt, User};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function profileQuiz(array $attrs = []): Quiz
{
    return Quiz::create(array_merge([
        'name' => 'Profile quiz', 'topic' => 'General', 'difficulty' => 'easy',
        'length' => 'short', 'type' => 'admin', 'questions_number' => 2,
    ], $attrs));
}

test('a user can view their profile and quiz statistics', function () {
    $user = User::factory()->create(['country' => 'Israel', 'year' => 4, 'graduated' => false]);
    $quiz = profileQuiz();
    $pending = QuizAttempt::create(['user_id' => $user->id, 'quiz_id' => $quiz->id, 'status' => 'pending', 'score' => 0]);
    $finished = QuizAttempt::create(['user_id' => $user->id, 'quiz_id' => $quiz->id, 'status' => 'finished', 'score' => 1]);
    $question = Questions::factory()->create();
    $user->playedQuestions()->attach($question);

    $response = $this->actingAs($user)->get(route('user.profile', $user));

    $response->assertOk()->assertSee('Israel')->assertSee('Year:')->assertSee('Quizzes Completed')
        ->assertSee('Complete')->assertSee('your attempt')->assertSee('View Your')->assertSee('Attempt')
        ->assertViewHas('stats', fn ($stats) => (int) $stats->completed === 1 && (int) $stats->incomplete === 1)
        ->assertViewHas('user', fn ($viewUser) => $viewUser->played_questions_count === 1);
});

test('graduated users do not see their year', function () {
    $user = User::factory()->create(['year' => 6, 'graduated' => true]);

    $this->actingAs($user)->get(route('user.profile', $user))->assertOk()->assertDontSee('Year:');
});

test('profile accuracy is based only on answered questions', function () {
    $user = User::factory()->create();
    $quiz = profileQuiz();
    $attempt = QuizAttempt::create(['user_id' => $user->id, 'quiz_id' => $quiz->id, 'status' => 'finished', 'score' => 1]);
    // Answer rows are created with valid foreign keys in the application schema.
    $questions = Questions::factory()->count(2)->create();
    $options = $questions->map(fn ($question) => $question->options()->create([
        'content' => 'Option', 'correct_answer' => true,
    ]));
    $attempt->answers()->createMany([
        ['question_id' => $questions[0]->id, 'option_id' => $options[0]->id, 'is_correct' => true, 'status' => 'answered'],
        ['question_id' => $questions[1]->id, 'option_id' => $options[1]->id, 'is_correct' => false, 'status' => 'answered'],
    ]);

    $this->actingAs($user)->get(route('user.profile', $user))->assertOk()
        ->assertViewHas('answerStats', fn ($stats) => (int) $stats->correct === 1 && (int) $stats->answered === 2);
});
