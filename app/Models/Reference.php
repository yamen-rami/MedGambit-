<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};

use Database\Factories\ReferenceFactory;

class Reference extends Model
{
    /** @use HasFactory<ReferenceFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function questions(): HasMany
    {
        return $this->hasMany(Questions::class);
    }
        public function game() : BelongsToMany{
        return $this->belongsToMany(Game::class , "game_references");
    }
}
