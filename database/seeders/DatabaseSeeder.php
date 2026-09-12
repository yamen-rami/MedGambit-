<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\{BranchOfMedicine, Option, Questions, SkillsForQuestion, Specialty, User};

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

        Questions::factory(1000)->create();
        User::factory()->create([
            "name" => "yamen" , 
            "country" => "ps",
            "know_about_us" => "social",
            "email" => "yamen@gmail.com",
            'password' => "12345678",
        ]);
        User::factory()->create([
            "name" => "jerj" , 
            "country" => "ps",
            "know_about_us" => "social",
            "email" => "jerj@gmail.com",
            'password' => "12345678",
        ]);
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
