<?php

use App\Models\User;
use Livewire\Livewire;

test('it change difficulty ', function () {
    $user = User::factory()->create();
    $c = Livewire::actingAs($user)
        ->test('config_game')
        ->set('difficulty', 'hard');

    $count = $c->get('questionBankCount');
    $this->assertEquals(20, $count);

});
test('it change length', function () {
    $user = User::factory()->create();
    $c = Livewire::actingAs($user)
        ->test('config_game')
        ->set('length', 'medium');

    $count = $c->get('questionBankCount');
    $this->assertEquals(20, $count);
});
test('it change branches', function () {
    $user = User::factory()->create();
    $c = Livewire::actingAs($user)
        ->test('config_game')
        ->set('branchesList', [1]);
    $count = $c->get('questionBankCount');
    $this->assertEquals(0, $count);
});
