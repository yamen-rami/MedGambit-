<?php

namespace App\Models;

use Database\Factories\BranchOfMedicineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BranchOfMedicine extends Model
{
    /** @use HasFactory<BranchOfMedicineFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Questions::class, 'question_BranchOfMedicine');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'games_branches');
    }
}
