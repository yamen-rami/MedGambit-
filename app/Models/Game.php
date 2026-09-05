<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Database\Factories\GameFactory;

class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    protected $fillable = ['max_players', 'status', "challenge_token" , 'started_at', 'ended_at' , "length" , "difficulty"];
    // TODO Duration 
    protected $casts = [
        "difficulty" => "array",
        "length" => "array",
    ];

    public function questions()
    {
        return $this->belongsToMany(Questions::class, 'game_questions', null, 'question_id');
    }

    // todo Game Attepmt
    public function attempts(){
        return $this->hasMany(GameAttempt::class);
    }
    public function players()
    {
        return $this->hasMany(Players::class);
    }

    // public function answers()
    // {
    //     return $this->hasMany(GameAnswers::class);
    // }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'games_specialities');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(BranchOfMedicine::class, 'games_branches');
}

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(SkillsForQuestion::class, 'games_skills');
    }

    public function scopePlayersCount(Builder $query): Builder
    {
        return $query->players()->count();
    }
    public function finishedPlayers(){
        return $this->players->where("status" , "finished")->count();
    }
}
