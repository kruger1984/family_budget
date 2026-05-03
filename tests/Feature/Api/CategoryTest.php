<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Family;
use App\Models\User;

it('lists system categories and current family categories', function (): void {
    $user = User::factory()->create();
    $family = Family::factory()->create();
    $user->families()->attach($family->id);

    Category::factory()->create(['name' => 'System Food', 'family_id' => null]);

    Category::factory()->create(['name' => 'Family Hobby', 'family_id' => $family->id]);

    Category::factory()->create([
        'name' => 'Other Family Category',
        'family_id' => Family::factory(),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('X-Family-Id', (string) $family->id)
        ->getJson('/api/categories');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonFragment(['name' => 'System Food'])
        ->assertJsonFragment(['name' => 'Family Hobby'])
        ->assertJsonMissing(['name' => 'Other Family Category']);
});

it('creates a category for the active family', function (): void {
    $user = User::factory()->create();
    $family = Family::factory()->create();
    $user->families()->attach($family->id);

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('X-Family-Id', (string) $family->id)
        ->postJson('/api/categories', [
            'name' => 'New Family Category',
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('categories', [
        'name' => 'New Family Category',
        'family_id' => $family->id,
    ]);
});

it('prevents deleting a system category', function (): void {
    $user = User::factory()->create();
    $systemCategory = Category::factory()->create(['family_id' => null]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/categories/$systemCategory->id");

    $response->assertForbidden();
});

it('ensures child category belongs to the same family as parent', function (): void {
    $user = User::factory()->create();
    $family = Family::factory()->create();
    $otherFamily = Family::factory()->create();

    $user->families()->attach([$family->id, $otherFamily->id]);

    $parent = Category::factory()->create(['family_id' => $family->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->withHeader('X-Family-Id', (string) $otherFamily->id)
        ->postJson('/api/categories', [
            'name' => 'Subcategory',
            'parent_id' => $parent->id,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['parent_id']);
});

it('can show a single category details', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['family_id' => null]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('data.name', $category->name);
});

it('can update a family category', function (): void {
    $user = User::factory()->create();
    $family = Family::factory()->create();
    $user->families()->attach($family->id);

    $category = Category::factory()->create(['family_id' => $family->id]);

    $this->actingAs($user, 'sanctum')
        ->withHeader('X-Family-Id', (string) $family->id)
        ->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Name',
        ])
        ->assertOk();

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Name']);
});

it('forbids updating system categories', function (): void {
    $user = User::factory()->create();
    $systemCategory = Category::factory()->create(['family_id' => null]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/categories/{$systemCategory->id}", ['name' => 'New Name'])
        ->assertForbidden();
});

it('forbids updating categories of another family', function (): void {
    $user = User::factory()->create();
    $otherFamilyCategory = Category::factory()->create(); // Чужа сім'я

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/categories/{$otherFamilyCategory->id}", ['name' => 'New Name'])
        ->assertForbidden();
});
