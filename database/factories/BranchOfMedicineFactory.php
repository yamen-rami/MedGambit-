<?php

namespace Database\Factories;

use App\Models\BranchOfMedicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BranchOfMedicine>
 */
class BranchOfMedicineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name('male'),
        ];
    }
}
