<?php

use App\Models\GameAttempt;
use App\Models\Option;
use App\Models\Questions;
use App\Models\User;
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
        Schema::create('game_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Questions::class, 'question_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'player_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Option::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(GameAttempt::class, 'game_attempt_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_correct');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_answers');
    }
};
