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
        $this->assertTrue($endedAt->equalTo($startedAt->copy()->addSeconds(120)));
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

    public function test_a_game_answer_is_saved_and_question_navigation_is_available(): void
    {
        [$game, $player, $opponent, $attempt] = $this->playingGame();
        $firstQuestion = Questions::factory()->create();
        $secondQuestion = Questions::factory()->create();
        $game->questions()->attach([$firstQuestion->id, $secondQuestion->id]);
        $option = $firstQuestion->options()->firstOrFail();

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->call('submit', $option->id, $firstQuestion->id)
            ->assertSet("answers.{$firstQuestion->id}", $option->id)
            ->call('next')
            ->assertSet('current', 2)
            ->call('previous')
            ->assertSet('current', 1);

        $this->assertDatabaseHas('game_answers', [
            'game_attempt_id' => $attempt->id,
            'player_id' => $player->id,
            'question_id' => $firstQuestion->id,
            'option_id' => $option->id,
        ]);
    }

    public function test_question_html_and_the_image_preview_are_rendered(): void
    {
        [$game, $player] = $this->playingGame();
        $question = Questions::factory()->create([
            'content' => '<strong>Important clinical finding</strong>',
            'image' => 'https://example.test/clinical-image.png',
        ]);
        $game->questions()->attach($question);

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->assertSee('<strong>Important clinical finding</strong>', false)
            ->assertSee('See image')
            ->assertSee('IMAGE CAPTION');
    }

    public function test_presence_callbacks_show_and_clear_the_opponent_offline_state(): void
    {
        [$game, $player, $opponent] = $this->playingGame();
        $game->questions()->attach(Questions::factory()->create());

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->call('playerConnected')
            ->call('playerDisconnected', $opponent->id)
            ->assertSet('opponentOnline', false);

        $this->assertNotNull($game->fresh()->disconnected_at);

        Livewire::test('game', ['gameId' => $game->id])
            ->call('playerConnected', $opponent->id)
            ->assertSet('opponentOnline', true);

        $this->assertNull($game->fresh()->disconnected_at);
    }

    public function test_both_players_start_online_when_the_game_has_no_disconnect_marker(): void
    {
        [$game, $player] = $this->playingGame();
        $game->questions()->attach(Questions::factory()->create());

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->assertSet('opponentOnline', true);
    }

    public function test_a_disconnect_marker_makes_the_initial_opponent_state_offline(): void
    {
        [$game, $player] = $this->playingGame(['disconnected_at' => now()]);
        $game->questions()->attach(Questions::factory()->create());

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->assertSet('opponentOnline', false);
    }

    public function test_presence_here_with_both_players_marks_the_opponent_online(): void
    {
        [$game, $player, $opponent] = $this->playingGame(['disconnected_at' => now()]);
        $game->questions()->attach(Questions::factory()->create());

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->call('syncPresence', [$player->id, $opponent->id])
            ->assertSet('opponentOnline', true);

        $this->assertNull($game->fresh()->disconnected_at);
    }

    public function test_presence_here_without_the_opponent_marks_them_offline(): void
    {
        [$game, $player, $opponent] = $this->playingGame();
        $game->questions()->attach(Questions::factory()->create());

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->call('syncPresence', [$player->id])
            ->assertSet('opponentOnline', false);

        $this->assertNotNull($game->fresh()->disconnected_at);
    }

    public function test_presence_callbacks_ignore_the_current_user_and_unknown_users(): void
    {
        [$game, $player, $opponent] = $this->playingGame();
        $game->questions()->attach(Questions::factory()->create());
        $unknown = User::factory()->create();

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->call('playerDisconnected', $player->id)
            ->call('playerDisconnected', $unknown->id)
            ->call('playerConnected', $unknown->id)
            ->assertSet('opponentOnline', true);

        $this->assertNull($game->fresh()->disconnected_at);
    }

    public function test_a_game_with_only_one_player_does_not_report_an_opponent_online(): void
    {
        $player = User::factory()->create(['gender' => 'male']);
        $game = Game::create(['status' => 'playing', 'max_players' => 2]);
        Players::create(['game_id' => $game->id, 'user_id' => $player->id, 'status' => 'playing']);
        GameAttempt::create(['game_id' => $game->id, 'user_id' => $player->id, 'status' => 'playing']);
        $game->questions()->attach(Questions::factory()->create());

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->assertSet('opponentOnline', false);
    }

    public function test_the_disconnect_reconnect_window_is_rendered(): void
    {
        [$game, $player] = $this->playingGame(['disconnected_at' => now()->subSeconds(30)]);
        $game->questions()->attach(Questions::factory()->create());

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->assertSee('Reconnect window')
            ->assertSee('disconnectRemaining');
    }

    public function test_the_game_ends_when_the_disconnect_window_expires(): void
    {
        [$game, $player] = $this->playingGame(['disconnected_at' => now()->subSeconds(121)]);
        $game->questions()->attach(Questions::factory()->create());

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->call('expireDisconnectedOpponent')
            ->assertRedirect(route('game.results', $game));

        $this->assertSame('finished', $game->fresh()->status);
    }

    public function test_the_alpine_timer_can_finish_an_expired_game_through_livewire(): void
    {
        [$game, $player, $opponent] = $this->playingGame(['ended_at' => now()->subSecond()]);
        $game->questions()->attach(Questions::factory()->create());

        $this->actingAs($player);

        Livewire::test('game', ['gameId' => $game->id])
            ->call('expireGame')
            ->assertRedirect(route('game.results', $game));

        $this->assertSame('finished', $game->fresh()->status);
        $this->assertDatabaseHas('game_attempts', ['game_id' => $game->id, 'user_id' => $player->id, 'status' => 'finished']);
        $this->assertDatabaseHas('game_attempts', ['game_id' => $game->id, 'user_id' => $opponent->id, 'status' => 'finished']);
    }

    /** @return array{Game, User, User, GameAttempt} */
    private function playingGame(array $attributes = []): array
    {
        $player = User::factory()->create(['gender' => 'male']);
        $opponent = User::factory()->create(['gender' => 'female']);
        $game = Game::create(array_merge([
            'status' => 'playing',
            'max_players' => 2,
            'started_at' => now()->subMinute(),
            'ended_at' => now()->addMinute(),
        ], $attributes));
        Players::create(['game_id' => $game->id, 'user_id' => $player->id, 'status' => 'playing']);
        Players::create(['game_id' => $game->id, 'user_id' => $opponent->id, 'status' => 'playing']);
        $attempt = GameAttempt::create(['game_id' => $game->id, 'user_id' => $player->id, 'status' => 'playing', 'started_at' => now()->subMinute()]);
        GameAttempt::create(['game_id' => $game->id, 'user_id' => $opponent->id, 'status' => 'playing', 'started_at' => now()->subMinute()]);

        return [$game, $player, $opponent, $attempt];
    }
}
