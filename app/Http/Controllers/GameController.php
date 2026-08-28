<?php

namespace App\Http\Controllers;

use App\Events\GameStarted;
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
            'hard',
            'medium',
            20
        );

        return redirect()->route('gameStarted', $game);
    }

    public function gameStarted(Game $game)
    {   
            abort_unless($game->players()->where('user_id', auth()->id())->exists(), 403);

        
        $gameId = $game->id ; 
        return view('games/startGame', compact('gameId'));
    }
    public function gameResults(Game $game , Players $player){
        // TODO RESULTS PAGE 
        dd($game , $player);
    }
}
