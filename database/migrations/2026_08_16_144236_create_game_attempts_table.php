<?php

use App\Models\Game;
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
        Schema::create('game_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Game::class, 'game_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_winner')->nullable()->default(false);
            $table->unsignedBigInteger('score')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('current_rank')->default(0);
            $table->unsignedBigInteger('new_rank')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_attempts');
    }
};
