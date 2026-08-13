<?php

use App\Models\Questions;
use App\Models\Specialty;
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
        Schema::create('question_speciality', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Questions::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Specialty::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_speciality');
    }
};
