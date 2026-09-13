<?php

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\Schema::table('game_answers', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->unique(
                ['game_attempt_id', 'player_id', 'question_id'],
                'game_answers_attempt_player_question_unique'
            );
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\Schema::table('game_answers', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropUnique('game_answers_attempt_player_question_unique');
        });
    }
};
