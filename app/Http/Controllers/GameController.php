<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Players;
use App\Services\GameService;

class GameController extends Controller
{
    public function __construct(public GameService $gameService) {}

    public function startGame()
    {
        $user = auth()->user();
        $game = $this->gameService->searchOrCreate(
            $user,
            ['easy', 'medium'],
            ['short', 'medium'],
            20
        );

        return redirect()->route('gameStarted', $game);
    }

    public function gameStarted(Game $game)
    {
        abort_unless($game->players()->where('user_id', auth()->id())->exists(), 403);

        $gameId = $game->id;

        return view('games/startGame', compact('gameId'));
    }

    public function gameResults(Game $game)
    {
        //
        $game->loadMissing('players', 'attempts', 'questions');
        // dd($game);
        if ($game->status !== 'completed') {
            abort(404, 'Game Status Is Playing');
        }
        // if(!$game->players()->where("user_id" , auth()->id())->exists()){
        //     abort(403 , "You Are Not Authroize");
        // }
        // dd($game->attempts);
        $winnerAttempt = $game->attempts()->with('user')->where('is_winner', true)->first();
        if (! $winnerAttempt) {
            return;
        }
        $winner = $winnerAttempt->user;
        $attempts = $game->attempts()->with('user', 'answers')->orderBy('is_winner', 'desc')->get();

        $questions = $game->questions;

        return view('games.gameResult', compact('game', 'winner', 'attempts', 'questions'));
    }

    public function friendGameStarted(string $challenge_token)
    {
        $game = Game::with('players', 'attempts', 'questions')
            ->where('challenge_token', $challenge_token)
            ->first();

        if (! $game) {
            abort(404, 'Game Not Found');
        }
        if ($game->players->where('user_id', auth()->id())) {
            return redirect()->route('friendGame', ['challenge_token' => $game->challenge_token]);

        }
        if ($game->status == 'finished') {
            abort(404, 'GAME Has Finished');
        }
        if ($game->challenge_token !== $challenge_token) {
            abort(403, 'Something Went Wrong');
        }

        $this->gameService->joinFriendGame($game);
        $gameId = $game->id;

        return redirect()->route('friendGame', ['challenge_token' => $game->challenge_token]);
    }

    public function friendGame(string $challnge_token)
    {
        $game = Game::where('challenge_token', $challnge_token)->first();
        dd($game);
        if ($game->status == 'finished') {
            abort(404, 'there is no games found');
        }

        abort_unless($game->players()->where('user_id', auth()->id())->exists(), 403);

        $gameId = $game->id;

        return view('games/startGame', compact('gameId'));
    }

    // Config Page
    public function config()
    {
        return view('games.config_game');
    }

    // Waiting Page For player 1
    public function waiting(Game $game)
    {
        return view('games.waiting', compact('game'));
    }
}
