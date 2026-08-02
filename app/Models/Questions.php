<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, Scope};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOne};

class Questions extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionsFactory> */
    use HasFactory;
    protected $fillable = [
        "content",
        "image",
        "topic",
        "main_explanation",
        "high_yield",
        "start_time",
        "end_time",
        "solved",
        "difficulty",
        "length",
        "elo_correct",
        "elo_incorrect",
        "reference",
    ];
    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }
    public function correctAnswer(): HasOne
    {
        return $this->hasOne(Option::class)->where("correct_answer", 1);
    }
    public function quizez(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, "quizez_questions");
    }
    // TODO QUIZEZ PIVOT TABLE 
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, "question_speciality");
    }
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(BranchOfMedicine::class, "question_BranchOfMedicine");
    }
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(SkillsForQuestion::class, "question_skills");
    }
    public function answers(): HasMany
    {
        return  $this->hasMany(Answers::class);
    }
}
