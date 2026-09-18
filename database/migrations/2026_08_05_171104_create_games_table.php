<?php

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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('max_players')->default(2);
            $table->integer("count")->default(20);
            $table->timestamp('disconnected_at')->nullable();
            $table->text('challenge_token')->unique()->nullable();
            $table->enum('status', ['pending', 'playing', 'finished', 'aborted'])->index();
            $table->unsignedBigInteger('duration')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'nerd'])->nullable();
            $table->enum('length', ['short', 'medium', 'long'])->nullable();
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
        Schema::dropIfExists('games');
    }
};
