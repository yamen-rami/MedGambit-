<?php

use App\Models\Answers;
use App\Models\Game;
use App\Models\GameAttempt;
use App\Models\Players;
use App\Models\Questions;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function requestedPagesQuiz(string $type = 'detected'): Quiz
{
    return Quiz::create([
        'name' => 'Requested pages quiz',
        'topic' => 'Clinical medicine',
        'difficulty' => 'medium',
        'length' => 'medium',
        'type' => $type,
        'questions_number' => 1,
    ]);
}

function requestedPagesAttempt(User $user, Quiz $quiz, string $status = 'pending'): QuizAttempt
{
    return QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'status' => $status,
        'score' => $status === 'finished' ? 1 : 0,
        'current' => 1,
        'finished_at' => $status === 'finished' ? now() : null,
    ]);
}

test('guests can view the login and registration pages', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Welcome back')
        ->assertSee('id="loginForm"', false);

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Create your MedGambit account')
        ->assertSee('id="registerForm"', false);
});

test('a user can log in and register with the required profile details', function () {
    $user = User::factory()->create([
        'email' => 'login@example.test',
        'password' => 'password',
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);

    $this->post(route('logout'));

    $this->post(route('register.store'), [
        'name' => 'Registered Student',
        'email' => 'registered@example.test',
        'password' => 'password',
        'password_confirmation' => 'password',
        'graduated' => 'false',
        'year' => 4,
        'gender' => 'female',
        'country' => 'Israel',
        'know_about_us' => 'friend',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Registered Student',
        'email' => 'registered@example.test',
        'graduated' => false,
        'year' => 4,
    ]);
});

test('an authenticated user can view quiz and game configuration', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('start.quiz'))
        ->assertOk()
        ->assertSee('Configure Exam')
        ->assertSee('Start learning quiz');

    $this->actingAs($user)
        ->get(route('config.game'))
        ->assertOk()
        ->assertSee('Friend Game')
        ->assertSee('Game timer');
});

test('a game player can view the game page', function () {
    $player = User::factory()->create();
    $opponent = User::factory()->create();
    $game = Game::create(['status' => 'playing', 'max_players' => 2]);
    Players::create(['game_id' => $game->id, 'user_id' => $player->id, 'status' => 'playing']);
    Players::create(['game_id' => $game->id, 'user_id' => $opponent->id, 'status' => 'playing']);
    GameAttempt::create(['game_id' => $game->id, 'user_id' => $player->id, 'status' => 'playing']);
    GameAttempt::create(['game_id' => $game->id, 'user_id' => $opponent->id, 'status' => 'playing']);
    $game->questions()->attach(Questions::factory()->create());

    $this->actingAs($player)
        ->get(route('gameStarted', $game))
        ->assertOk()
        ->assertSee('Question');
});

test('an authenticated user can view start and learning quiz pages', function () {
    $user = User::factory()->create();
    $question = Questions::factory()->create(['name' => 'Question for quiz pages']);

    $startQuiz = requestedPagesQuiz();
    $startQuiz->questions()->attach($question);
    requestedPagesAttempt($user, $startQuiz);

    $this->actingAs($user)
        ->get(route('show.quiz', $startQuiz))
        ->assertOk()
        ->assertSee('Question for quiz pages');

    $learningQuiz = requestedPagesQuiz('learning');
    $learningQuiz->questions()->attach($question);
    requestedPagesAttempt($user, $learningQuiz);

    $this->actingAs($user)
        ->get(route('start.learning.quiz', $learningQuiz))
        ->assertOk()
        ->assertSee('Question for quiz pages');
});

test('a quiz owner can view quiz results', function () {
    $user = User::factory()->create();
    $quiz = requestedPagesQuiz('admin');
    $question = Questions::factory()->create(['name' => 'Results question']);
    $quiz->questions()->attach($question);
    $attempt = requestedPagesAttempt($user, $quiz, 'finished');
    $correctOption = $question->correctAnswer()->firstOrFail();
    Answers::create([
        'quiz_attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'option_id' => $correctOption->id,
        'is_correct' => true,
        'status' => 'answered',
    ]);

    $this->actingAs($user)
        ->get(route('quizResult', $quiz))
        ->assertOk()
        ->assertSee('Quiz results')
        ->assertSee('Results question');
});

test('a game player can view completed game results', function () {
    $winner = User::factory()->create(['name' => 'Game winner']);
    $loser = User::factory()->create(['name' => 'Game loser']);
    $game = Game::create(['status' => 'finished', 'max_players' => 2]);
    Players::create(['game_id' => $game->id, 'user_id' => $winner->id, 'status' => 'finished']);
    Players::create(['game_id' => $game->id, 'user_id' => $loser->id, 'status' => 'finished']);
    GameAttempt::create([
        'game_id' => $game->id,
        'user_id' => $winner->id,
        'status' => 'finished',
        'score' => 1,
        'is_winner' => true,
        'started_at' => now()->subMinute(),
    ]);
    GameAttempt::create([
        'game_id' => $game->id,
        'user_id' => $loser->id,
        'status' => 'finished',
        'score' => 0,
        'is_winner' => false,
        'started_at' => now()->subMinute(),
    ]);
    $game->questions()->attach(Questions::factory()->create());

    $this->actingAs($winner)
        ->get(route('game.results', $game))
        ->assertOk()
        ->assertSee('BATTLE COMPLETE')
        ->assertSee('Game winner');
});

test('a profile is available with its quiz statistics', function () {
    $user = User::factory()->create(['name' => 'Profile student', 'country' => 'Israel']);
    $quiz = requestedPagesQuiz('admin');
    requestedPagesAttempt($user, $quiz, 'finished');

    $this->actingAs($user)
        ->get(route('user.profile', $user))
        ->assertOk()
        ->assertSee('Profile student')
        ->assertSee('COMPLETED QUIZZES');
});
