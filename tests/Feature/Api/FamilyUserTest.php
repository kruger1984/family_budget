<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Family;
use App\Models\User;

it('allows owner to generate an invite code', function (): void {
    $user = User::factory()->create();
    $family = Family::factory()->create(['owner_id' => $user->id]);
    $user->families()->attach($family->id, ['role' => Role::Owner]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/families/$family->id/invitations");

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['code', 'expires_at']]);

    $this->assertDatabaseHas('family_invitations', [
        'family_id' => $family->id,
    ]);
});

it('allows a user to join a family using a valid code', function (): void {
    $owner = User::factory()->create();
    $family = Family::factory()->create(['owner_id' => $owner->id]);

    $invitation = $family->invitations()->create([
        'code' => 'JOIN123',
        'expires_at' => now()->addDay(),
    ]);

    $newUser = User::factory()->create();

    $response = $this->actingAs($newUser, 'sanctum')
        ->postJson('/api/families/join', ['code' => 'JOIN123']);

    $response->assertOk();

    $this->assertDatabaseHas('family_user', [
        'family_id' => $family->id,
        'user_id' => $newUser->id,
        'role' => Role::Member->value,
    ]);

    $this->assertDatabaseMissing('family_invitations', ['id' => $invitation->id]);
});

it('prevents joining with an expired code', function (): void {
    $family = Family::factory()->create();
    $family->invitations()->create([
        'code' => 'EXPIRED',
        'expires_at' => now()->subMinute(),
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/families/join', ['code' => 'EXPIRED']);

    $response->assertStatus(410);
});

it('allows owner to revoke an invitation code', function (): void {
    $user = User::factory()->create();
    $family = Family::factory()->create(['owner_id' => $user->id]);
    $invitation = $family->invitations()->create([
        'code' => 'REVOKE_ME',
        'expires_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/family-invitations/{$invitation->id}");

    $response->assertOk();
    $this->assertDatabaseMissing('family_invitations', ['id' => $invitation->id]);
});
