<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Database\Factories\BranchOfMedicineFactory;

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
