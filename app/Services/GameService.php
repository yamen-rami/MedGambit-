<?php

namespace App\Services;

use App\Models\{Game, User};

class GameService
{
  public function searchOrCreate(User $user)
  {
    $game = Game::where("status", "pending")->first();
    if (empty($game)) {
      $createGame = Game::creat([
        "player_1" => $user,
        "player_2" => null,
        "status" => "pending",
      ]);
      
      return $createGame ;
    }
    return $game; 
  }
}
