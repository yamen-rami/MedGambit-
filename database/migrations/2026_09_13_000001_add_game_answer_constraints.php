<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_answers', function (Blueprint $table) {
            $table->unique(
                ['game_attempt_id', 'player_id', 'question_id'],
                'game_answers_attempt_player_question_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('game_answers', function (Blueprint $table) {
            $table->dropUnique('game_answers_attempt_player_question_unique');
        });
    }
};
