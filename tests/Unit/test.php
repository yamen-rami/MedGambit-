<?php

use App\Models\Questions;
use App\Models\User;
use App\Services\QuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('return 20 question ', function () {
    $this->actingAs(User::factory()->create());
    $service = new QuizService;
    $quiz = $service->detectedQuiz(
        Questions::factory()->count(20)->create(),
        'short',
        'short',
        20,
        'hard',
        null,
    );

    expect($quiz->questions)->toHaveCount(20);
});
