<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Database\Factories\SpecialtyFactory;

class Specialty extends Model
{
    /** @use HasFactory<SpecialtyFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Questions::class, 'question_speciality');
    }
     public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'games_specialities');
    }
}
