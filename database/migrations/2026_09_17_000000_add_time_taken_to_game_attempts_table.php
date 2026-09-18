<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_attempts', function (Blueprint $table) {
            $table->unsignedInteger('time_taken')->default(0)->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('game_attempts', function (Blueprint $table) {
            $table->dropColumn('time_taken');
        });
    }
};
