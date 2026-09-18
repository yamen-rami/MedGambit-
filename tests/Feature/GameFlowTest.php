<?php

use App\Models\Game;
use App\Models\GameAttempt;
use App\Models\Players;
use App\Models\Questions;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a player can open a game page', function () {
    $user = User::factory()->create();
    $game = Game::create(['status' => 'playing', 'max_players' => 2]);
    Players::create(['game_id' => $game->id, 'user_id' => $user->id, 'status' => 'playing']);
    GameAttempt::create(['game_id' => $game->id, 'user_id' => $user->id, 'status' => 'playing']);
    $game->questions()->attach(Questions::factory()->create());

    $this->actingAs($user)
        ->get(route('gameStarted', $game))
        ->assertOk();
});

test('the challenge owner sees a copyable challenge URL', function () {
    $owner = User::factory()->create();
    $game = Game::create([
        'status' => 'pending',
        'max_players' => 2,
        'challenge_token' => 'owner-challenge-token',
        'difficulty' => 'medium',
        'length' => 'medium',
    ]);
    Players::create(['game_id' => $game->id, 'user_id' => $owner->id, 'status' => 'playing']);
    GameAttempt::create(['game_id' => $game->id, 'user_id' => $owner->id, 'status' => 'playing']);

    $this->actingAs($owner)
        ->get(route('friend.game.started', $game->challenge_token))
        ->assertOk()
        ->assertSee('Copy link')
        ->assertSee('window.location.href');
});

test('a second user joins by opening the challenge URL', function () {
    $owner = User::factory()->create();
    $opponent = User::factory()->create();
    $game = Game::create([
        'status' => 'pending',
        'max_players' => 2,
        'challenge_token' => 'join-challenge-token',
        'difficulty' => 'medium',
        'length' => 'medium',
    ]);
    Players::create(['game_id' => $game->id, 'user_id' => $owner->id, 'status' => 'playing']);
    GameAttempt::create(['game_id' => $game->id, 'user_id' => $owner->id, 'status' => 'playing']);
    Questions::factory()->count(2)->create(['difficulty' => 'medium', 'length' => 'medium']);

    $this->actingAs($opponent)
        ->get(route('friend.game.started', $game->challenge_token))
        ->assertRedirect(route('gameRedirect', $game->challenge_token));

    $this->assertDatabaseHas('players', ['game_id' => $game->id, 'user_id' => $opponent->id]);
    $this->assertDatabaseHas('game_attempts', ['game_id' => $game->id, 'user_id' => $opponent->id]);
    expect($game->fresh()->status)->toBe('playing');
});

test('the highest scoring player wins a game', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();
    $game = Game::create(['status' => 'playing', 'max_players' => 2]);
    $firstAttempt = GameAttempt::create([
        'game_id' => $game->id, 'user_id' => $first->id, 'status' => 'finished', 'score' => 3,
        'time_taken' => 40,
    ]);
    $secondAttempt = GameAttempt::create([
        'game_id' => $game->id, 'user_id' => $second->id, 'status' => 'finished', 'score' => 2,
        'time_taken' => 20,
    ]);

    $winner = app(GameService::class)->getWinner(
        GameAttempt::whereKey([$firstAttempt->id, $secondAttempt->id])->get()
    );

    expect($winner->is($firstAttempt))->toBeTrue();
});

test('a finished game results page shows the winner', function () {
    $winner = User::factory()->create(['name' => 'Winning Player']);
    $loser = User::factory()->create(['name' => 'Losing Player']);
    $game = Game::create(['status' => 'finished', 'max_players' => 2]);
    Players::create(['game_id' => $game->id, 'user_id' => $winner->id, 'status' => 'finished']);
    Players::create(['game_id' => $game->id, 'user_id' => $loser->id, 'status' => 'finished']);
    GameAttempt::create([
        'game_id' => $game->id, 'user_id' => $winner->id, 'status' => 'finished', 'score' => 3,
        'is_winner' => true, 'started_at' => now()->subMinute(),
    ]);
    GameAttempt::create([
        'game_id' => $game->id, 'user_id' => $loser->id, 'status' => 'finished', 'score' => 1,
        'is_winner' => false, 'started_at' => now()->subMinute(),
    ]);
    $game->questions()->attach(Questions::factory()->create());

    $this->actingAs($winner)
        ->get(route('game.results', $game))
        ->assertOk()
        ->assertSee('Winning Player')
        ->assertSee('WINNER');
});
