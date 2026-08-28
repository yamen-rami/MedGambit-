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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('topic');
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'nerd']);
            $table->enum('length', ['short', 'medium', 'long']);
            $table->enum('type', ['random', 'detected', 'admin', 'learning' , "game"]);
            $table->integer('questions_number');
            $table->unsignedBigInteger('duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
