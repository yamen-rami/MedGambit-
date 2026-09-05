<?php

namespace App\Models;

use Database\Factories\ReferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reference extends Model
{
    /** @use HasFactory<ReferenceFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function questions(): HasMany
    {
        return $this->hasMany(Questions::class);
    }
}
