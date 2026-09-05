<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Events\GameStarted;
use App\Models\{Game, GameAttempt, Players, Questions, User};

class GameService
{
    public function searchOrCreate(
        User $user,
        ?array $difficulty = null,
        ?array $length = null,
        ?int $duration = null,
        ?Collection $sp = null,
        ?Collection $branches = null,
        ?Collection $skills = null
    ) {
        return DB::transaction(function () use ($user, $difficulty, $length, $duration, $sp, $branches, $skills) {
            $game = Game::where('status', 'pending')
                ->whereDoesntHave('players', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereNull('challenge_token')
                ->lockForUpdate()
                ->first();

            if (! $game) {
                $game = Game::create([
                    'status' => 'pending',
                    'max_players' => 2,
                    'duration' => $duration,
                    'ended_at' => null,
                ]);
                $this->createPlayer($user->id, $game->id);
                $this->createAttempt($game->id, $user->id);

                return $game;
            }

            $this->createPlayer($user->id, $game->id);
            $this->createAttempt($game->id, $user->id);

            $players = $game->players()->with('user')->get();
            if ($players->count() !== 2) {
                return;
            }
            $player1 = $players[0]->user;
            $player2 = $players[1]->user;

            $questions = $this->createQuiz(
                player_1: $player1,
                player_2: $player2,
                duration: $duration,
                difficulty: $difficulty,
                length: $length,
                specialties: $sp,
                branches: $branches,
                skills: $skills
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
        ?array $difficulty = null,
        ?array $length = null,
        ?Collection $specialties = null ,
        ?Collection $skills = null ,
        ?Collection $branches = null,
    ) {
        
        $player_1->loadMissing('playedQuestions');
        $player_2->loadMissing('playedQuestions');
        $p1 = $player_1->playedQuestions->pluck('id')->toArray();
        $p2 = $player_2->playedQuestions->pluck('id')->toArray();
        $ignore = array_merge($p1, $p2);
        $questions = Questions::query()
            ->when($difficulty, fn ($query) => $query->whereIn('difficulty', $difficulty))
            ->when($length, fn ($query) => $query->whereIn('length', $length))
            // TODO A Good Duration
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

    public function createPlayer(int $user, int $gameId)
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
            'ended_at' => $game->duration ? $now->addSeconds($game->duration) : null,
        ]);
        $game->attempts->each(function ($attempt) use ($now) {
            $attempt->update([
                'started_at' => $now,
            ]);
        });
        $players->each(function ($player) use ($now) {
            $player->update([
                'started_at' => $now,
            ]);
        });
    }

    public function editAttempt(GameAttempt $attempt, Game $game): void
    {
        $now = now();
        $user = User::findOrFail($attempt->user_id);

        $attempt->loadMissing('answers.question');
        $game_rank = $user->game_rank;
        $questionIds = [];
        foreach ($attempt->answers as $answer) {
            $questionIds[] = $answer->question->id;
            if ($answer->is_correct) {
                $game_rank += $answer->question->elo_correct;

            } else {
                $game_rank -= $answer->question->elo_incorrect;
            }
        }
        $user->playedQuestions()->syncWithoutDetaching($questionIds);
        $user->update([
            'game_rank' => $game_rank,
        ]);

        $correct = $attempt->answers->where('is_correct', true)->count();
        $wrong = $attempt->answers->where('is_correct', false)->count();
        $attempt->update([
            'ended_at' => $now,
            'status' => 'finished',
            'time_taken' => $attempt->started_at->diffInSeconds($now),
            'score' => $correct,
        ]);

        $game->loadMissing('players');
        $player = $game->players->where('user_id', $attempt->user_id)->first();

        if (! $player) {
            return;
        }

        $player->update([
            'status' => 'finished',
            'correct_answers' => $correct,
            'wrong_answers' => $wrong,
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

    public function friendGame(?array $difficulty, ?array $length, $duration = null, $sp = null, $branches = null, $skills = null)
    {
        $game = Game::create([
            'status' => 'pending',
            'max_players' => 2,
            'challenge_token' => Str::random(32),
            'difficulty' => $difficulty,
            'length' => $length,
            'duration' => $duration,
        ]);
        $userId = auth()->id();
        $this->createPlayer($userId, $game->id);
        $this->createAttempt($game->id, $userId);
        $game->specialties()->attachOrFail($sp);
        $game->branches()->attachOrFail($branches);
        $game->skills()->attachOrFail($skills);

        return $game;
    }

    public function joinFriendGame(Game $game)
    {
        $userId = auth()->id();

        return DB::transaction(function () use ($game, $userId) {
            $this->createPlayer($userId, $game->id);
            $this->createAttempt($game->id, $userId);
            $game->load('players.user', 'branches', 'skills', 'specialties');
            $questions = $this->createQuiz(
                $game->players[0]->user,
                $game->players[1]->user,
                $game->duration,
                $game->difficulty,
                $game->length,
                $game->specialties->count() > 0  ? $game->specialties : null,
                $game->skills->count() > 0  ? $game->skills : null,
                $game->branches->count() > 0  ? $game->branches : null,
            );
            $game->questions()->attach($questions->pluck('id'));
            $this->gameStarted($game, $game->players);
            $game->update([
                'status' => 'playing',
                'started_at' => now(),
            ]);
            DB::afterCommit(fn () => GameStarted::dispatch($game));
            return $game;
        });
    }

    public function friendChallenge(string $challenge_token)
    {
        $game = Game::with('players', 'attempts', 'questions')
            ->where('challenge_token', $challenge_token)
            ->where('status', 'pending')
            ->first();

        if (! $game) {
            abort(404, 'Challenge Not Found');
        }

        if ($game->status !== 'pending') {
            abort(403, 'Game Is Not Pending');
        }

        if ($game->players()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You Are Already In This Game');
        }

        if ($game->players->count() > 1) {
            return;
        }

        $this->joinFriendGame($game);

        return redirect()->route('friend.game.started', [
            'game' => $game,
            'challenge_token' => $game->challenge_token,
        ]);
    }

    public function friendGameStarted(Game $game, string $challenge_token)
    {
        if ($game->challenge_token !== $challenge_token) {
            abort(403, 'Something Went Wrong');
        }

        if (! $game->players()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You Are Not In This Game');
        }

        return view('game.challengeStarted', compact('game'));
    }
}
