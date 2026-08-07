<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\GameService;

class GameController extends Controller
{
    public function __construct(public GameService $gameService  )
    {
    }
    public function startGame(){
        $game = $this->gameService->searchOrCreate(auth()->user());
        return view("games.startGame" , compact("game"));
        }
    //
}
