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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('image')->nullable();
            $table->text('topic');
            $table->text('main_explanation');
            $table->text('high_yield');
            $table->boolean('solved')->default(false);
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'nerd'])->index();
            $table->enum('length', ['short', 'medium', 'long']);
            $table->enum('elo_correct', [4, 8, 12]);
            $table->enum('elo_incorrect', [5, 10, 15]);
            $table->foreignId('reference_id')->constrained()->cascadeOnDelete();
            $table->fullText(['content', 'topic']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
