<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialty extends Model
{
    /** @use HasFactory<\Database\Factories\SpecialtyFactory> */
    use HasFactory;
    protected  $fillable = ["name"]; 

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Questions::class, "question_speciality");
    }
}
