<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

use App\Events\GameStarted;
use App\Models\{Game, GameAttempt, Players, Questions, User};

class GameService
{
    public function searchOrCreate(
        User $user,
        ?string $difficulty = null,
        ?string $length = null,
        ?int $duration = null,
        ?Collection $sp = null,
        ?Collection $branches = null,
        ?Collection $skills = null
    ) {
        return DB::transaction(function () use ($user, $difficulty, $length, $sp, $branches, $skills , $duration) {
            $game = Game::where('status', 'pending')
                ->whereDoesntHave('players', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->lockForUpdate()
                ->first();
            if (! $game) {
                $game = Game::create([
                    'status' => 'pending',
                    'max_players' => 2,
                    'ended_at' => null,
                ]);
                $this->createPlayer($user->id, $game->id);
                $this->createAttempt($game->id, $user->id);

                return $game;
            }
            $this->createPlayer($user->id, $game->id);

            $this->createAttempt($game->id, $user->id);

            
            $players = $game->players()->with('user')->get();
            $player1 = $players[0]->user;
            $player2 = $players[1]->user;

            $questions = $this->createQuiz(
                player_1 : $player1,
                player_2 : $player2,
                difficulty: $difficulty,
                length: $length,
                specialties: $sp,
                branches : $branches,
                skills : $skills
            );
            $game->questions()->attach($questions->pluck('id'));

            $game->update([
                'status' => 'playing',
                'started_at' => now(),
            ]);
            DB::afterCommit(fn () => GameStarted::dispatch($game));

            return $game;
        });
    }

    public function createQuiz(
        ?User $player_1,
        ?User $player_2,
        ?int $duration = null,
        ?string $difficulty = null,
        ?string $length = null,
        ?Collection $specialties = null,
        ?Collection $skills = null,
        ?Collection $branches = null,

    ) {
        $player_1->loadMissing('playedQuestions');
        $player_2->loadMissing('playedQuestions');
        $p1 = $player_1->playedQuestions->pluck('id')->toArray();
        $p2 = $player_2->playedQuestions->pluck('id')->toArray();
        $ignore = array_merge($p1, $p2);
        $questions = Questions::query()
            ->when($difficulty, fn ($query) => $query->where('difficulty', $difficulty))
            ->when($length, fn ($query) => $query->where('length', $length))
            // TODO There are things that more importantth
            ->when($branches, function ($query) use ($branches) {
                $query->whereHas('branches', function ($q) use ($branches) {
                    $q->whereIn('branch_of_medicines.id', $branches->pluck('id'));
                });
            })
            ->when($skills, function ($query) use ($skills) {
                $query->whereHas('skills', function ($q) use ($skills) {
                    $q->whereIn('skills_for_questions.id', $skills->pluck('id'));
                });
            })
            ->when($specialties, function ($query) use ($specialties) {
                $query->whereHas('specialties', function ($q) use ($specialties) {
                    $q->whereIn('specialties.id', $specialties->pluck('id'));
                });
            })
            ->whereNotIn('id', $ignore)
            ->limit(20)
            ->get();

        return $questions;
    }

    public function createPlayer($user, $gameId)
    {
        Players::create([
            'user_id' => $user,
            'game_id' => $gameId,
            'status' => 'playing',
        ]);
    }

    public function createAttempt(int $gameId, int $userId)
    {
        GameAttempt::create([
            'game_id' => $gameId,
            'user_id' => $userId,
            'status' => 'playing',
        ]);
    }

    public function gameStarted(Game $game, Collection $players)
    {
        $now = now();
        $game->update([
            'started_at' => $now,
            "ended_at" => $game->duration ? $now->addSeconds($game->duration): null ,
        ]);
        $players->each(function($player) use($now) {
            $player->update([
                "started_at" => $now ,
            ]);
        });
    }

    public function editAttempt(GameAttempt $attempt, Game $game): void
    {
        $now = now();

        $attempt->update([
            'ended_at' => $now,
            'status' => 'finished',
            'time_taken' => $attempt->started_at->diffInSeconds($now),
        ]);
        $game->loadMissing('players');
        $player = $game->players->where('user_id', $attempt->user_id)->first();
        if (! $player) {
            return;
        }
        $player->update([
            'status' => 'finished',
        ]);
    }

    public function getWinner(Collection $attempts)
    {
        $winner = $attempts
            ->loadMissing('answers')
            ->sortBy([
                fn ($attempt) => -$attempt->answers->where('is_correct', true)->count(),
                fn ($attempt) => $attempt->time_taken,
            ])
            ->first();

        return $winner;
    }

    public function finishGame(Collection $attempts)
    {
        $winner = $this->getWinner($attempts);
        if (! $winner) {
            return;
        }
        $winner->update([
            'is_winner' => true,
        ]);
        $winner->game()->update([
            'status' => 'completed',
        ]);
    }

    }
