<?php

declare(strict_types=1);

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Enums\Role;
use App\Models\Account;
use App\Models\Family;
use App\Models\User;

it('does not return accounts from other families when family context is active', function (): void {
    $user = User::factory()->create();

    $family = Family::factory()->create();
    $user->families()->attach($family->id);

    $account = Account::factory()->create(['family_id' => $family->id]);
    $personal = Account::factory()->create(['user_id' => $user->id, 'family_id' => null]);

    $otherFamily = Family::factory()->create();
    $otherAccount = Account::factory()->create(['family_id' => $otherFamily->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('X-Family-Id', (string) $family->id)
        ->getJson('/api/accounts');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $account->id])
        ->assertJsonFragment(['id' => $personal->id])
        ->assertJsonMissing(['id' => $otherAccount->id]);
});

it('returns only personal accounts when user has no family', function (): void {
    $user = User::factory()->create();

    $personalAccount = Account::factory()->create(['user_id' => $user->id]);
    $otherAccount = Account::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/accounts');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $personalAccount->id])
        ->assertJsonMissing(['id' => $otherAccount->id]);
});

it('user can create personal account', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/accounts', [
            'name' => 'My account',
            'type' => AccountType::Cash,
            'currency' => Currency::USD,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $user->id);
});

it('only family owner can create family account', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $family = Family::factory()->create(
        ['owner_id' => $owner->id]
    );

    $owner->families()->attach($family->id, ['role' => Role::Owner]);
    $member->families()->attach($family->id, ['role' => Role::Member]);

    // ❌ 2. member пробує створити (має бути заборонено)
    $this->actingAs($member, 'sanctum')
        ->withHeader('X-Family-Id', (string) $family->id)
        ->postJson('/api/accounts', [
            'family_id' => $family->id,
            'name' => 'Member trying to create account',
            'type' => AccountType::Cash,
            'currency' => Currency::USD,
        ])
        ->assertForbidden();

    // ✅ 3. owner пробує створити (має бути дозволено)
    $this->actingAs($owner, 'sanctum')
        ->withHeader('X-Family-Id', (string) $family->id)
        ->postJson('/api/accounts', [
            'family_id' => $family->id,
            'name' => 'Owner creating account',
            'type' => AccountType::Cash,
            'currency' => Currency::USD,
        ])
        ->assertCreated();
});

it('forbids access to foreign family with header', function (): void {
    $user = User::factory()->create();

    $foreignFamily = Family::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('X-Family-Id', (string) $foreignFamily->id)
        ->getJson('/api/accounts');

    $response->assertForbidden();
});

it('user can update their own personal account', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id, 'family_id' => null]);

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/accounts/{$account->id}", [
            'name' => 'New Name',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'New Name');
});

it('user cannot update someone else personal account', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $otherUser->id, 'family_id' => null]);

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/accounts/{$account->id}", [
            'name' => 'Hacker Name',
        ])
        ->assertForbidden();
});

it('member cannot update family accounts created by owner', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $family = Family::factory()->create(['owner_id' => $owner->id]);

    $owner->families()->attach($family->id, ['role' => Role::Owner]);
    $member->families()->attach($family->id, ['role' => Role::Member]);

    $account = Account::factory()->create([
        'user_id' => $owner->id,
        'family_id' => $family->id,
    ]);

    $this->actingAs($member, 'sanctum')
        ->patchJson("/api/accounts/{$account->id}", [
            'name' => 'Member Trying',
        ])
        ->assertForbidden();
});
