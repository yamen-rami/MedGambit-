<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Players extends Model
{
    protected $fillable = ['user_id', 'game_id', 'current_question', 'correct_answers', 'wrong_answers', 'time_taken' , "status"];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function attempts()
    {
        return $this->hasMany(GameAttempt::class);
    }

}
