<?php

namespace App\Models;

use Database\Factories\QuestionsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Questions extends Model
{
    /** @use HasFactory<QuestionsFactory> */
    use HasFactory;

    protected $fillable = [
        'content',
        'image',
        'topic',
        'main_explanation',
        'high_yield',
        'difficulty',
        'length',
        'elo_correct',
        'elo_incorrect',
        'reference_id',
    ];

    public function playedCount(): HasOne
    {
        return $this->hasOne(QuestionPlayedTime::class, 'question_id');
    }

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_questions');
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function correctAnswer(): HasOne
    {
        return $this->hasOne(Option::class)->where('correct_answer', 1);
    }

    public function quizez(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'quizez_questions');
    }

    // TODO QUIZEZ PIVOT TABLE
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'question_speciality');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(BranchOfMedicine::class, 'question_BranchOfMedicine');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(SkillsForQuestion::class, 'question_skills');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answers::class);
    }

    public function gameAnswers(): HasMany
    {
        return $this->hasMany(GameAnswers::class);
    }

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_played_questions');
    }

    public function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class);
    }
}
