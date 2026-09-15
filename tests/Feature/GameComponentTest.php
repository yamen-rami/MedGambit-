<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameAnswers;
use App\Models\GameAttempt;
use App\Models\Players;
use App\Models\Questions;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GameComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_starting_a_game_is_idempotent(): void
    {
        $game = Game::create([
            'status' => 'pending',
            'max_players' => 2,
            'duration' => 120,
        ]);
        $first = User::factory()->create(['gender' => 'male']);
        $second = User::factory()->create(['gender' => 'female']);
        Players::create(['game_id' => $game->id, 'user_id' => $first->id, 'status' => 'playing']);
        Players::create(['game_id' => $game->id, 'user_id' => $second->id, 'status' => 'playing']);
        $firstAttempt = GameAttempt::create(['game_id' => $game->id, 'user_id' => $first->id, 'status' => 'playing']);
        $secondAttempt = GameAttempt::create(['game_id' => $game->id, 'user_id' => $second->id, 'status' => 'playing']);

        $service = app(GameService::class);
        $service->startGame($game);
        $game->refresh();
        $startedAt = $game->started_at;
        $endedAt = $game->ended_at;

        $service->startGame($game->refresh());

        $this->assertTrue($startedAt->equalTo($game->refresh()->started_at));
        $this->assertTrue($endedAt->equalTo($game->ended_at));
        $this->assertNotNull($firstAttempt->refresh()->started_at);
        $this->assertNotNull($secondAttempt->refresh()->started_at);
    }

    public function test_an_answer_cannot_be_inserted_twice_for_one_question(): void
    {
        $user = User::factory()->create(['gender' => 'male']);
        $game = Game::create(['status' => 'playing', 'max_players' => 2]);
        $attempt = GameAttempt::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'status' => 'playing',
        ]);
        $question = Questions::factory()->create();
        $option = $question->options()->firstOrFail();

        GameAnswers::create([
            'game_attempt_id' => $attempt->id,
            'player_id' => $user->id,
            'question_id' => $question->id,
            'option_id' => $option->id,
            'is_correct' => $option->correct_answer,
        ]);

        $this->expectException(QueryException::class);

        GameAnswers::create([
            'game_attempt_id' => $attempt->id,
            'player_id' => $user->id,
            'question_id' => $question->id,
            'option_id' => $option->id,
            'is_correct' => $option->correct_answer,
        ]);
    }

    public function test_a_non_player_cannot_view_game_results(): void
    {
        $player = User::factory()->create(['gender' => 'male']);
        $visitor = User::factory()->create(['gender' => 'female']);
        $game = Game::create(['status' => 'finished', 'max_players' => 2]);
        Players::create(['game_id' => $game->id, 'user_id' => $player->id, 'status' => 'finished']);

        $this->actingAs($visitor)
            ->get(route('game.results', $game))
            ->assertForbidden();
    }

    public function test_game_component_mounts_for_a_game_player(): void
    {
        $player = User::factory()->create(['gender' => 'male']);
        $opponent = User::factory()->create(['gender' => 'female']);
        $game = Game::create([
            'status' => 'playing',
            'max_players' => 2,
            'started_at' => now(),
            'ended_at' => now()->addMinutes(20),
        ]);
        Players::create(['game_id' => $game->id, 'user_id' => $player->id, 'status' => 'playing']);
        Players::create(['game_id' => $game->id, 'user_id' => $opponent->id, 'status' => 'playing']);
        $attempt = GameAttempt::create([
            'game_id' => $game->id,
            'user_id' => $player->id,
            'status' => 'playing',
            'started_at' => now(),
        ]);
        GameAttempt::create([
            'game_id' => $game->id,
            'user_id' => $opponent->id,
            'status' => 'playing',
            'started_at' => now(),
        ]);
        $question = Questions::factory()->create();
        $game->questions()->attach($question);

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->assertSet('gameId', $game->id)
            ->assertSet('attempt.id', $attempt->id)
            ->assertSet('current', 1)
            ->assertSet('loading', false);
    }
}
