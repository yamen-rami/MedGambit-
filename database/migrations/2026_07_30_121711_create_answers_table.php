<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\{Option, Questions, QuizAttempt};

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(QuizAttempt::class, 'quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Questions::class, 'question_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Option::class, 'option_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_correct');
            $table->string('status');
            $table->unique(['quiz_attempt_id', 'question_id']);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
