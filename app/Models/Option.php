<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Option extends Model
{
    use HasFactory;

    //  ? fillable
    protected $fillable = ['name', 'content', 'correct_answer', 'image', 'explanation', 'questions_id'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Questions::class);
    }

    public function answers(): HasOne
    {
        return $this->hasOne(Answers::class);
    }
}
