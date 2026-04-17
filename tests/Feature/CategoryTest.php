<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\CategoryChildFamilyException;
use App\Models\Category;
use App\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_system_and_family_category(): void
    {
        $family = Family::factory()->create();

        $systemCategory = Category::factory()->create();
        $familyCategory = Category::factory()->create([
            'family_id' => $family->id,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $systemCategory->id,
            'family_id' => null,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $familyCategory->id,
            'family_id' => $family->id,
        ]);
    }

    public function test_category_can_have_subcategories(): void
    {
        // Arrange
        $parent = Category::factory()->create([
            'name' => 'Transport',

        ]);

        // Act
        $child = Category::factory()->create([
            'name' => 'Fuel',
            'parent_id' => $parent->id,
        ]);

        // Assert
        $this->assertTrue($parent->children->contains($child));
        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_category_cannot_have_different_family_than_parent(): void
    {
        $family1 = Family::factory()->create();
        $family2 = Family::factory()->create();

        $parentCategory = Category::factory()->create([
            'name' => 'Transport',
            'family_id' => $family1->id,
        ]);

        $this->expectException(CategoryChildFamilyException::class);

        Category::factory()->create([
            'name' => 'Fuel',
            'parent_id' => $parentCategory->id,
            'family_id' => $family2->id,
        ]);
    }

    public function test_children_deleted_with_parent(): void
    {
        // Arrange
        $parent = Category::factory()->create(['name' => 'Transport']);

        // Act
        $child = Category::factory()->create([
            'name' => 'Fuel',
            'parent_id' => $parent->id,
        ]);
        $parent->delete();

        // Assert
        $this->assertDatabaseMissing('categories', [
            'id' => $child->id,
        ]);
    }
}
