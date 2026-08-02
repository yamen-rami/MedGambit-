<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BranchOfMedicine extends Model
{
    /** @use HasFactory<\Database\Factories\BranchOfMedicineFactory> */
    use HasFactory;
    protected $fillable = ["name"];
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Questions::class, "question_BranchOfMedicine");
    }
}
