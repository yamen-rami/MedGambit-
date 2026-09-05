<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionPlayedTime extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionPlayedTimeFactory> */
    use HasFactory;
    protected $fillable = ['count' , 'question_id'];
    public function question(){
        return $this->belongsTo(Questions::class);
    }
}
