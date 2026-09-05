<?php

use App\Models\Game;
use App\Models\Questions;
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
        Schema::create('game_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Game::class)->constrained()->cascadeOnUpdate();
            $table->foreignIdFor(Questions::class, 'question_id')->constrained()->cascadeOnUpdate();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_questions');
    }
};
