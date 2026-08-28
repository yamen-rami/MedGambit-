<?php

use Illuminate\Support\Facades\Broadcast;

use App\Models\{Game, Players};

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
Broadcast::channel('game.{gameId}', function ($user, $gameId) {
     return Players::where("game_id" , $gameId)->where("user_id" , $user->id)
        ->exists();
});
Broadcast::channel('game.finished.{gameId}', function ($user, $gameId) {
     return Players::where("game_id" , $gameId)->where("user_id" , $user->id)
        ->exists();
});
Broadcast::channel('playerAnswerd.{gameId}', function ($user, $gameId) {
    return Players::where("game_id" , $gameId)->where("user_id" , $user->id)
        ->exists();
});
// Broadcast::channel('game.{gameId}', function ($user, $gameId) {
//     dd($user->id, $gameId);

//     return true;
// });