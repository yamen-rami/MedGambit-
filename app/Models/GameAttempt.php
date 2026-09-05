<?php

namespace App\Models;

use Database\Factories\GameAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameAttempt extends Model
{
    /** @use HasFactory<GameAttemptFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'game_id', 'is_winner', 'status', 'score', 'started_at', 'ended_at'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function winner()
    {
        return $this->where('is_winner', true)->first();
    }

    public function answers()
    {
        return $this->hasMany(GameAnswers::class);
    }
    // The relation itself is wrong
    // cause it users

}
