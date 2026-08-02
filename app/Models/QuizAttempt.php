<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class QuizAttempt extends Model
{
    //
    protected $fillable = [
        "quiz_id",
        'user_id',
        "started_at",
        "finished_at",
        "time_taken",
        "score",
        "status",
    ];
    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
    public function answers(): HasMany
    {
        return  $this->hasMany(Answers::class);
    }
}
