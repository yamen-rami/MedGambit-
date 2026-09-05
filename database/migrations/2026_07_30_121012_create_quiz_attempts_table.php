<?php

use App\Models\Quiz;
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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            // Start
            $table->foreignIdFor(Quiz::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->integer('current')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('score');
            $table->unsignedBigInteger('current_rank')->default(0);
            $table->unsignedBigInteger('new_rank')->default(0);
            $table->unsignedInteger('time_taken')->nullable();
            $table->string('status');
            $table->unsignedInteger('correctCount')->default(0);
            $table->unsignedInteger('wrongCount')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
