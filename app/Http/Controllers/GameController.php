<?php

namespace App\Http\Controllers;

use App\Models\{Game, Players};
use App\Services\GameService;

class GameController extends Controller
{
    public function __construct(public GameService $gameService) {}

    public function startGame()
    {
        $user = auth()->user();
        $game = $this->gameService->searchOrCreate(
            $user,
            'medium',
            'medium'
        );

        return redirect()->route('gameStarted', $game);
    }

    public function gameStarted(Game $game)
    {
        abort_unless($game->players()->where('user_id', auth()->id())->exists(), 403);
        $gameId = $game->id;

        // For Random Players
        return view('games/startGame', compact('gameId'));
    }

    public function gameResults(Game $game)
    {
        //
        $game->loadMissing('players', 'attempts', 'questions');
        if ($game->status !== 'completed') {
            abort(404, 'Game Status Is Playing');
        }
        $winnerAttempt = $game->attempts()->with('user')->where('is_winner', true)->first();
        if (! $winnerAttempt) {
            return;
        }
        $winner = $winnerAttempt->user;
        $attempts = $game->attempts()->with('user', 'answers')->orderBy('is_winner', 'desc')->get();

        $questions = $game->questions;

        return view('games.gameResult', compact('game', 'winner', 'attempts', 'questions'));
    }

    public function friendGameCreate(string $challenge_token)
    {
        $user = auth()->user();

        $game = Game::where('challenge_token', $challenge_token)->firstOrFail();

        if ($game->players()->where('user_id', $user->id)->exists()) {
            return redirect()->route('gameRedirect', [
                'challenge_token' => $game->challenge_token,
            ]);
        }

    }

    public function friendGame(string $challenge_token)
    {
        $user = auth()->user() ;
        $game = Game::where('challenge_token', $challenge_token)->first();
        abort_unless(
            $game->players()->count() < $game->max_players,
            403,
            'You Are Not Allowed'
        );
        $h = $this->gameService->joinFriend($user->id, $game->id);

        return redirect()->route('gameRedirect', [
            'challenge_token' => $game->challenge_token,
        ]);

    }
    public function gameRedirect(string $challenge_token){
        $game = Game::where('challenge_token', $challenge_token)->first();
        if($game->status === "finished"){
            abort(401 , "Game Has Finished");
        }
        $gameId = $game->id ; 

        return view('games.startGame' , compact("gameId"));
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
