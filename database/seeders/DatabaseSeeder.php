<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\{BranchOfMedicine, Option, Questions, SkillsForQuestion, Specialty, User};
use Database\Factories\BranchOfMedicineFactory;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Option::factory(1000)->create();
        Questions::factory(100)
            // ->hasAttached(
            //     SkillsForQuestion::factory()->count(4),
            //     [],
            //     'skills'
            // )
            // ->hasAttached(
            //     BranchOfMedicine::factory()->count(4),
            //     [],
            //     'branches'
            // )
            // ->hasAttached(
            //     Specialty::factory()->count(4),
            //     [],
            //     'specialties'
            // )
            ->create();
    }
}
