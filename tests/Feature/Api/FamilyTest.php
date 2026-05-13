<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Family;
use App\Models\User;

it('does not return families of other users', function (): void {
    $myUser = User::factory()->create();
    $otherUser = User::factory()->create();

    $myFamily = Family::factory()->create(['name' => 'Моя сім\'я']);
    $otherFamily = Family::factory()->create(['name' => 'Чужа сім\'я']);

    $myUser->families()->attach($myFamily->id, ['role' => Role::Owner]);
    $otherUser->families()->attach($otherFamily->id, ['role' => Role::Owner]);

    $response = $this->actingAs($myUser, 'sanctum')
        ->getJson('/api/families');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $myFamily->id)
        ->assertJsonPath('data.0.name', 'Моя сім\'я');
});

it('returns a list of user families with their roles', function (): void {
    $user = User::factory()->create();

    $family = Family::factory()->create(['name' => 'Шевченки']);

    $user->families()->attach($family->id, [
        'role' => Role::Owner,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/families');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'role',
                    'owner' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'users' => [
                        0 => [
                            'id',
                            'name',
                            'email',
                        ],
                    ],
                ],
            ],
            'meta',
            'message',
            'errors',
        ])
        ->assertJsonPath('data.0.name', 'Шевченки')
        ->assertJsonPath('data.0.role', Role::Owner->value);
});

it('requires a name to create a family (Validation)', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/families');

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
        ])
        ->assertJsonStructure([
            'errors' => ['name'],
        ]);
});

it('can create a family and automatically assigns owner role (Logic & Contract)', function (): void {
    $user = User::factory()->create(['name' => 'Олександр']);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/families', [
            'name' => 'Родина Коваленків',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Родина Коваленків')
        ->assertJsonPath('data.role', Role::Owner->value)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'role',
                'owner' => [
                    'id',
                    'name',
                    'email',
                ],
                'users' => [
                    0 => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ],
        ])->assertJsonPath('data.owner.id', $user->id)
        ->assertJsonPath('data.owner.name', 'Олександр');

    $familyId = $response->json('data.id');

    $this->assertDatabaseHas('families', [
        'id' => $familyId,
        'name' => 'Родина Коваленків',
    ]);

    $this->assertDatabaseHas('family_user', [
        'family_id' => $familyId,
        'user_id' => $user->id,
        'role' => Role::Owner,
    ]);
});

it('cannot view a family the user does not belong to (Isolation)', function (): void {
    $user = User::factory()->create();
    $otherFamily = Family::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/families/$otherFamily->id");

    $response->assertStatus(403);
});

it('can view its own family details (Contract)', function (): void {
    $user = User::factory()->create();
    $family = Family::factory()->create([
        'name' => 'Моя улюблена сім\'я',
    ]);
    $user->families()->attach($family->id, ['role' => Role::Owner]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/families/$family->id");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'role',
                'owner' => [
                    'id',
                    'name',
                    'email',
                ],
                'users' => [
                    0 => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ],
        ])
        ->assertJsonPath('data.name', 'Моя улюблена сім\'я')
        ->assertJsonPath('data.role', Role::Owner->value);
});

it('prevents regular members from updating the family name (Authorization)', function (): void {
    $user = User::factory()->create();
    $family = Family::factory()->create(['name' => 'Стара назва']);

    $user->families()->attach($family->id, ['role' => Role::Member]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/families/$family->id", [
            'name' => 'Нова назва',
        ]);

    $response->assertStatus(403);

    $this->assertDatabaseHas('families', [
        'id' => $family->id,
        'name' => 'Стара назва',
    ]);
});

it('allows owner to update the family name (Logic)', function (): void {
    $user = User::factory()->create();

    $family = Family::factory()->create([
        'name' => 'Стара назва',
        'owner_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/families/$family->id", [
            'name' => 'Нова супер назва',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Нова супер назва');
});

it('prevents regular members from deleting the family (Authorization)', function (): void {
    $user = User::factory()->create();
    $family = Family::factory()->create();

    $user->families()->attach($family->id, ['role' => Role::Member]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/families/$family->id");

    $response->assertStatus(403);

    $this->assertDatabaseHas('families', [
        'id' => $family->id,
    ]);
});

it('allows owner to delete the family and cleans up records (Logic)', function (): void {
    $user = User::factory()->create();

    $family = Family::factory()->create([
        'owner_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/families/$family->id");

    $response->assertNoContent();

    $this->assertDatabaseMissing('families', [
        'id' => $family->id,
    ]);
});
