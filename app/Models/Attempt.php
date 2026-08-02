<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany};

class Attempt extends Model
{
    /** @use HasFactory<\Database\Factories\AttemptFactory> */
    use HasFactory;
    protected $fillable = ['quiz_id', "user_id", "start_time", "end_time", "points"];
    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }
    /* 
        What about the quiz 
        every attempt should only include one quiz 
    */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
