<?php

use App\Models\Reference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function referenceAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

test('the reference creation form submits to the reference endpoint', function () {
    $this->actingAs(referenceAdmin())
        ->get(route('references.create'))
        ->assertOk()
        ->assertSee('action="'.route('references.store').'"', false);
});

test('an admin can create a reference', function () {
    $this->actingAs(referenceAdmin())
        ->post(route('references.store'), ['name' => 'Clinical Reference'])
        ->assertRedirect(route('references.index'));

    $this->assertDatabaseHas('references', ['name' => 'Clinical Reference']);
});

test('an admin can update and delete a reference', function () {
    $admin = referenceAdmin();
    $reference = Reference::factory()->create(['name' => 'Old Reference']);

    $this->actingAs($admin)
        ->patch(route('references.update', $reference), ['name' => 'Updated Reference'])
        ->assertRedirect(route('references.index'));

    $this->assertDatabaseHas('references', [
        'id' => $reference->id,
        'name' => 'Updated Reference',
    ]);

    $this->actingAs($admin)
        ->delete(route('references.destroy', $reference))
        ->assertRedirect(route('references.index'));

    $this->assertDatabaseMissing('references', ['id' => $reference->id]);
});
