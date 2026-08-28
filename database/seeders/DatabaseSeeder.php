<?php

namespace Database\Seeders;

use App\Models\BranchOfMedicine;
use App\Models\Option;
use App\Models\Questions;
use App\Models\SkillsForQuestion;
use App\Models\Specialty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        BranchOfMedicine::factory(100)->create();
        Specialty::factory(100)->create();
        SkillsForQuestion::factory(100)->create();

        // Option::factory(1000)->create();
        Questions::factory(10000)->create();
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
    }
}
