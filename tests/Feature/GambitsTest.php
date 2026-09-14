<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\{Questions, User};
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
it('searches gambits', function () {
    $user = User::factory()->create();

    $heartQuestion = Questions::factory()->create([
        'name' => 'Heart failure treatment',
    ]);

    $brainQuestion = Questions::factory()->create([
        'name' => 'Brain tumor diagnosis',
    ]);

    Livewire::actingAs($user)
        ->test('gambits')
        ->set('search', 'Heart')
        ->assertSee($heartQuestion->name)
        ->assertDontSee($brainQuestion->name);
});