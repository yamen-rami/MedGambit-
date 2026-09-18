<?php

use App\Models\Questions;
use App\Models\Reference;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);
it('filters gambits by difficulty', function () {

    $user = User::factory()->create();

    $easyQuestion = Questions::factory()->create([
        'difficulty' => 'easy',
        'name' => 'This is the easy question',
    ]);

    $hardQuestion = Questions::factory()->create([
        'difficulty' => 'hard',
        'name' => 'This is the hard question',
    ]);

    Livewire::actingAs($user)
        ->test('gambits')
        ->set('difficulty', 'hard')
        ->assertSee('This is the hard question')
        ->assertDontSee('This is the easy question');
});
it('filters gambits by length', function () {

    $user = User::factory()->create();

    $easyQuestion = Questions::factory()->create([
        'length' => 'short',
        'name' => 'This is the easy question',
    ]);

    $hardQuestion = Questions::factory()->create([
        'length' => 'long',
        'name' => 'This is the hard question',
    ]);

    Livewire::actingAs($user)
        ->test('gambits')
        ->set('length', 'long')
        ->assertSee('This is the hard question')
        ->assertDontSee('This is the easy question');
});

it('filters gambits by specialty and reference', function () {
    $user = User::factory()->create();
    $cardiology = Specialty::factory()->create(['name' => 'Cardiology']);
    $neurology = Specialty::factory()->create(['name' => 'Neurology']);
    $boardReference = Reference::factory()->create(['name' => 'Board Review']);
    $otherReference = Reference::factory()->create(['name' => 'Other Review']);

    $matchingQuestion = Questions::factory()->create([
        'name' => 'Cardiology board question',
        'reference_id' => $boardReference->id,
    ]);
    $matchingQuestion->specialties()->attach($cardiology);

    $otherQuestion = Questions::factory()->create([
        'name' => 'Neurology reference question',
        'reference_id' => $otherReference->id,
    ]);
    $otherQuestion->specialties()->attach($neurology);

    Livewire::actingAs($user)
        ->test('gambits')
        ->set('sp', [$cardiology->id])
        ->set('references', [$boardReference->id])
        ->assertSee('Cardiology board question')
        ->assertDontSee('Neurology reference question');
});

it('shows the quiz controls and opens the quiz modal for selected questions', function () {
    $user = User::factory()->create();
    $question = Questions::factory()->create(['name' => 'Selected gambit question']);

    Livewire::actingAs($user)
        ->test('gambits')
        ->call('toggleQuestion', $question->id)
        ->assertSet('selectedQuestions', [$question->id])
        ->assertSee('Start Quiz')
        ->assertSee('1 selected')
        ->call('openQuizModal')
        ->assertSet('showQuizModal', true)
        ->assertSee('Quiz name')
        ->assertSee('Learning mode')
        ->assertSee('Exam mode');
});
