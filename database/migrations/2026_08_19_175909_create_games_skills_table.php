<?php

use App\Models\Game;
use App\Models\SkillsForQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SkillsForQuestion::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Game::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games_skills');
    }
};
