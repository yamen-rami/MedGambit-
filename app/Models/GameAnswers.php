<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameAnswers extends Model
{
    //
protected $fillable = ["player_id" , "question_id" , "game_attempt_id" , "option_id" , "is_correct"];
    public function gameAttempt(){
        return $this->belongsTo(GameAttempt::class);
    }
}
