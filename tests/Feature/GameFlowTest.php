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
