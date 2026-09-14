<?php

use App\Models\Questions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('searches gambits and only shows matching questions', function () {
    $user = User::factory()->create();

    Questions::factory()->create([
        'name' => 'Xylometazoline treatment',
        'content' => 'A question about xylometazoline treatment.',
        'topic' => 'Xylometazoline',
    ]);

    Questions::factory()->create([
        'name' => 'Neurology examination',
        'content' => 'A question about neurological examination.',
        'topic' => 'Neurology',
    ]);

    $this->assertDatabaseCount('questions', 2);

    Livewire::actingAs($user)
        ->test('gambits')
        ->set('search', 'Xylometazoline')
        ->assertSet('search', 'Xylometazoline')
        ->assertSee('Xylometazoline treatment')
        ->assertDontSee('Neurology examination');

});
