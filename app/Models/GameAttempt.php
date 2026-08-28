<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAttempt extends Model
{
    /** @use HasFactory<\Database\Factories\GameAttemptFactory> */
    use HasFactory;
    protected $fillable = ["user_id", "game_id", "is_winner" , "status" , "score"  ,"started_at" ,"ended_at"  ];
    protected function casts(): array
{
    return [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
    public function game(){
        return $this->belongsTo(Game::class);
    }
    public function user(){
        return $this->belongsTo(User::class , "player_1");
    } 
    public function answers(){
        return $this->hasMany(GameAnswers::class);
    }
    
}
