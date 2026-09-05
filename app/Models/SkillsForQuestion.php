<?php

namespace App\Models;

use Database\Factories\SkillsForQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SkillsForQuestion extends Model
{
    /** @use HasFactory<SkillsForQuestionFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Questions::class, 'questions_skills');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'games_skills');
    }
}
