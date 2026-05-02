<?php

declare(strict_types=1);

use App\Models\User;

it('returns user profile data in ApiResponse format', function (): void {
    $user = User::factory()->create(['name' => 'John Doe']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/auth/me');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => ['id', 'name', 'email'],
            'meta',
            'message',
            'errors',
        ])
        ->assertJsonPath('success', true)
        ->assertJsonPath('errors', null)
        ->assertJsonPath('data.name', 'John Doe');
});

it('can update profile name', function (): void {
    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'name' => 'New Name',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'New Name');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
    ]);
});

it('can delete user profile', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/profile');

    $response->assertNoContent();

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});
