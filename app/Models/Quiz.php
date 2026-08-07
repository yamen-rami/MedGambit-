<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};

class Quiz extends Model
{
    /** @use HasFactory<\Database\Factories\QuizFactory> */
    use HasFactory;
    protected $fillable = ["questions_number", "topic", "type" , "name", "difficulty", "length" , "duration"];
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Questions::class, "quizez_questions");
    }
    public function attempts(): HasMany
    {
        // If the user want to re attempt
        return $this->hasMany(QuizAttempt::class);
    }
    public function answers(): HasMany
    {
        return $this->hasMany(Answers::class);
    }
}
