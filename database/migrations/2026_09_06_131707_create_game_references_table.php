<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\{Game, Reference};

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_references', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Game::class , "game_id")->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Reference::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_references');
    }
};
