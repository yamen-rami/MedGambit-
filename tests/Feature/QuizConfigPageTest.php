<?php

use App\Models\Questions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the quiz config page with shared Select2 assets and fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('start.quiz'))
        ->assertOk()
        ->assertSee('id="branches"', false)
        ->assertSee('id="sp"', false)
        ->assertSee('id="skills"', false)
        ->assertSee('id="references"', false)
        ->assertSee('class="quiz-choice-group four"', false)
        ->assertSee('class="quiz-choice-group three"', false)
        ->assertSee(asset('assets/vendor/libs/select2/select2.js'), false)
        ->assertSee(asset('assets/css/select2-theme.css'), false)
        ->assertSee(asset('assets/js/select2-init.js'), false)
        ->assertDontSee('data-select2', false);
});

it('updates the question count when difficulty and length filters change', function () {
    $user = User::factory()->create();

    Questions::factory()->create([
        'name' => 'Matching question',
        'difficulty' => 'hard',
        'length' => 'long',
    ]);
    Questions::factory()->create([
        'name' => 'Different difficulty',
        'difficulty' => 'easy',
        'length' => 'long',
    ]);
    Questions::factory()->create([
        'name' => 'Different length',
        'difficulty' => 'hard',
        'length' => 'short',
    ]);

    Livewire::actingAs($user)
        ->test('quiz-config')
        ->call('setDifficulty', 'hard')
        ->call('setLength', 'long')
        ->assertSet('difficulty', 'hard')
        ->assertSet('length', 'long')
        ->assertSee('1 matching questions');
});

it('never accepts more than 20 questions from the client', function () {
    $user = User::factory()->create();
    Questions::factory()->count(30)->create([
        'difficulty' => 'medium',
        'length' => 'medium',
    ]);

    Livewire::actingAs($user)
        ->test('quiz-config')
        ->set('count', 150)
        ->assertSet('count', 20)
        ->assertSee('20 matching questions');
});

it('shows the authenticated user links in the quiz sidebar', function () {
    $user = User::factory()->create();
    $profileUrl = route('user.profile', $user);

    Livewire::actingAs($user)
        ->test('quiz-config')
        ->assertSee('Played Questions')
        ->assertSee('Review History')
        ->assertSee($profileUrl, false);
});
