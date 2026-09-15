<?php

use App\Models\Questions;
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
