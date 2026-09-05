<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Questions;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_played_times', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Questions::class , "question_id")->constrained("questions")->cascadeOnDelete();
            $table->unsignedBigInteger("count")->default(0); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_played_times');
    }
};
