<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Events\{GameStarted, connectedUsers};
use App\Models\{Game, GameAttempt, Players, Questions, User};

class GameService
{
    public function searchOrCreate(
        User $user,
        ?string $difficulty,
        ?string $length,
        ?int $duration,
        ?int $count,
        ?Collection $sp = null,
        ?Collection $branches = null,
        ?Collection $skills = null,
        ?Collection $references = null,
    ) {
        return DB::transaction(function () use ($user, $difficulty, $length, $duration, $sp, $branches, $skills, $references, $count) {
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
                    'difficulty' => $difficulty ?? 'easy',
                    'length' => $length ?? 'easy',
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
                difficulty: $difficulty,
                count: $count,
                length: $length,
                specialties: $sp,
                branches: $branches,
                skills: $skills,
                references : $references,
            );
            $game->questions()->attach($questions->pluck('id'));

            $this->startGame($game);
            DB::afterCommit(fn () => GameStarted::dispatch($game));

            return $game;
        });
    }

    public function createQuiz(
        ?User $player_1,
        ?User $player_2,
        ?string $difficulty,
        ?string $length,
        ?int $count = 20 ,
        ?Collection $specialties = null,
        ?Collection $skills = null,
        ?Collection $branches = null,
        ?Collection $references = null,
    ) {

        $player_1->loadMissing('playedQuestions');
        $player_2->loadMissing('playedQuestions');
        $p1 = $player_1->playedQuestions->pluck('id')->toArray();
        $p2 = $player_2->playedQuestions->pluck('id')->toArray();
        $ignore = array_merge($p1, $p2);
        $questions = Questions::query()
            ->when($difficulty, fn ($query) => $query->where('difficulty', $difficulty))
            ->when($length, fn ($query) => $query->where('length', $length))
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
            ->when($references, function ($query) use ($references) {
                $query->whereHas('reference', function ($q) use ($references) {
                    $q->whereIn('references.id', $references->pluck('id'));
                });
            })
            ->whereNotIn('id', $ignore)
            ->limit($count)
            ->get();
        $qCount = $questions->count();
        if ($qCount < $count) {
            $missingCount = $count - $qCount;

            $missingQuestions = Questions::query()
                // fallback should be LESS restrictive
                ->whereNotIn('id', $ignore)
                ->whereNotIn('id', $questions->pluck('id'))
                ->limit($missingCount)
                ->get();

            $questions = $questions->merge($missingQuestions);
        }

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

    public function startGame(Game $game): void
    {
        if ($game->status === 'playing' && $game->started_at) {
            return;
        }

        $now = now();
        $game->update([
            'status' => 'playing',
            'started_at' => $now,
            'ended_at' => $game->duration ? $now->copy()->addSeconds($game->duration) : null,
        ]);

        $game->attempts()->whereNull('started_at')->update([
            'started_at' => $now,
        ]);
    }

    public function editAttempt(GameAttempt $attempt, Game $game): void
    {
        DB::transaction(function () use ($attempt, $game): void {
            $attempt = GameAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if ($attempt->status === 'finished') {
                return;
            }

            $now = now();
            $user = User::query()->lockForUpdate()->findOrFail($attempt->user_id);

            $attempt->load('answers.question');
            $rank = $user->rank;
            $playedQuestionIds = $user->playedQuestions->pluck('id');

            $questionIds = [];
            foreach ($attempt->answers as $answer) {
                $questionIds[] = $answer->question->id;
                $question = $answer->question;
                if (! $playedQuestionIds->contains($question->id)) {
                    $rank += $answer->is_correct
                        ? $question->elo_correct
                        : -$question->elo_incorrect;
                }
            }

            $user->playedQuestions()->syncWithoutDetaching($questionIds);

            $correct = $attempt->answers->where('is_correct', true)->count();
            $wrong = $attempt->answers->where('is_correct', false)->count();
            $attempt->update([
                'ended_at' => $now,
                'status' => 'finished',
                'time_taken' => $attempt->started_at ? (int) $attempt->started_at->diffInSeconds($now) : 0,
                'score' => $correct,
                'current_rank' => $user->rank,
                'new_rank' => $rank,
            ]);
            $user->update(['rank' => $rank]);

            $player = $game->players()->where('user_id', $attempt->user_id)->first();
            if ($player) {
                $player->update([
                    'status' => 'finished',
                    'correct_answers' => $correct,
                    'wrong_answers' => $wrong,
                ]);
            }
        });
    }

    public function getWinner(Collection $attempts)
    {
        $winner = $attempts
            ->loadMissing('answers')
            ->sort(function ($first, $second) {
                return ($second->score <=> $first->score)
                    ?: ($first->time_taken <=> $second->time_taken);
            })
            ->first();

        return $winner;
    }

    /**
     * Pick the winner only after every player has completed their attempt.
     *
     * This method is intentionally idempotent because both clients can receive
     * the finished broadcast at roughly the same time.
     */
    public function finishGame(Collection $attempts): bool
    {
        if ($attempts->isEmpty()) {
            return false;
        }

        return DB::transaction(function () use ($attempts): bool {
            $lockedAttempts = GameAttempt::query()
                ->whereIn('id', $attempts->pluck('id'))
                ->lockForUpdate()
                ->get();

            if ($lockedAttempts->isEmpty()) {
                return false;
            }

            $game = Game::query()
                ->lockForUpdate()
                ->find($lockedAttempts->first()->game_id);

            if (! $game || $game->status === 'finished') {
                return $game?->status === 'finished';
            }

            if ($game->players()->where('status', '!=', 'finished')->exists()) {
                return false;
            }

            $winner = $this->getWinner($lockedAttempts);
            if (! $winner) {
                return false;
            }

            $lockedAttempts->each->update(['is_winner' => false]);
            $winner->update(['is_winner' => true]);
            $game->update(['status' => 'finished']);

            return true;
        });
    }

    /**
     * End an in-progress game (for example when its configured duration runs
     * out). Unfinished attempts are scored from the answers already saved.
     */
    public function completeGame(Game $game): bool
    {
        $game->refresh();

        if ($game->status === 'finished') {
            return true;
        }

        $attempts = $game->attempts()->with('answers.question')->get();
        foreach ($attempts->where('status', '!=', 'finished') as $attempt) {
            $player = $game->players()->where('user_id', $attempt->user_id)->first();
            $player?->update(['status' => 'finished']);
            $this->editAttempt($attempt, $game);
        }

        return $this->finishGame($game->fresh()->attempts);
    }

    public function friendGame(
        ?string $difficulty = null,
        ?string $length = null,
        $duration = null,
        $sp = null,
        $branches = null,
        $skills = null,
        $references = null,
        ?int $count,
    ) {
        return DB::transaction(function () use ($difficulty, $length, $duration, $sp, $branches, $skills, $references, $count) {
            $game = Game::create([
                'status' => 'pending',
                'max_players' => 2,
                'challenge_token' => Str::random(32),
                'difficulty' => $difficulty ? $difficulty : 'easy',
                'length' => $length ? $length : 'short',
                'duration' => $duration,
                "count" => $count ,
            ]);
            $userId = auth()->id();
            $this->createPlayer($userId, $game->id);
            $this->createAttempt($game->id, $userId);
            $game->specialties()->attachOrFail($sp);
            $game->branches()->attachOrFail($branches);
            $game->skills()->attachOrFail($skills);
            $game->references()->attachOrFail($references);

            return $game;
        });

    }

    public function joinFriend(int $userId, int $gameId)
    {
        return DB::transaction(function () use ($userId, $gameId) {
            $game = Game::query()
                ->whereKey($gameId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (! $game) {
                throw new \RuntimeException('Game is no longer available.');
            }

            if ($game->players()->where('user_id', $userId)->exists()) {
                throw new \RuntimeException('You are already in this game.');
            }

            if ($game->players()->count() >= $game->max_players) {
                throw new \RuntimeException('Game is full.');
            }

            $this->createPlayer($userId, $gameId);
            $this->createAttempt($gameId, $userId);

            $game->load('players.user');

            if ($game->players->count() !== $game->max_players) {
                return;
            }

            $questions = $this->createQuiz(
                player_1: $game->players[0]->user,
                player_2: $game->players[1]->user,
                difficulty: $game->difficulty,
                length: $game->length,
                count: $game->count ,
                specialties: $game->specialties->count() > 0 ? $game->specialties : null,
                branches: $game->branches->count() > 0 ? $game->branches : null,
                skills: $game->skills->count() > 0 ? $game->skills : null,
                references: $game->references->count() > 0 ? $game->references : null,
            );

            if ($questions->count() < 2) {
                throw new \RuntimeException('Not enough questions available.');
            }

            $game->questions()->attach($questions->pluck('id'));

            $this->startGame($game);

            DB::afterCommit(
                function () use ($game) {
                    GameStarted::dispatch($game->fresh());
                    connectedUsers::dispatch($game->id);
                }
            );
        });
    }

    public function getMessage(Collection $attempts): array
    {
        $attempts->loadMissing('answers', 'user');

        $player1 = $attempts->first();
        $player2 = $attempts->last();

        $player1Score = $player1->answers
            ->where('is_correct', true)
            ->count();

        $player2Score = $player2->answers
            ->where('is_correct', true)
            ->count();

        $player1Message = match (true) {
            $player1Score > $player2Score => 'You Are Winning',
            $player1Score < $player2Score => 'You Are Losing',
            default => 'Draw',
        };

        $player2Message = match (true) {
            $player2Score > $player1Score => 'You Are Winning',
            $player2Score < $player1Score => 'You Are Losing',
            default => 'Draw',
        };

        return [
            'player1' => [
                'user' => $player1->user,
                'message' => $player1Message,
                'winning' => $player1Score > $player2Score,
                'draw' => $player1Score === $player2Score,
            ],

            'player2' => [
                'user' => $player2->user,
                'message' => $player2Message,
                'winning' => $player2Score > $player1Score,
                'draw' => $player1Score === $player2Score,
            ],
        ];
    }
}
