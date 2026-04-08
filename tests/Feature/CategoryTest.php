<?php

namespace Tests\Feature;

use App\Enums\CategoryType;
use App\Exceptions\CategoryChildTypeException;
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
            'id'        => $systemCategory->id,
            'family_id' => null,
        ]);

        $this->assertDatabaseHas('categories', [
            'id'        => $familyCategory->id,
            'family_id' => $family->id,
        ]);
    }

    public function test_category_can_have_subcategories(): void
    {
        // Arrange
        $parent = Category::factory()->create([
            'name' => 'Transport',
            'type' => CategoryType::Income

        ]);

        // Act
        $child = Category::factory()->create([
            'name'      => 'Fuel',
            'parent_id' => $parent->id,
            'type' => CategoryType::Income
        ]);

        // Assert
        $this->assertTrue($parent->children->contains($child));
        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_children_deleted_with_parent(): void
    {
        // Arrange
        $parent = Category::factory()->create(['name' => 'Transport']);

        // Act
        $child = Category::factory()->create([
            'name'      => 'Fuel',
            'parent_id' => $parent->id,
            'type'      => $parent->type
        ]);
        $parent->delete();

        // Assert
        $this->assertDatabaseMissing('categories', [
            'id' => $child->id,
        ]);
    }

    public function test_child_must_have_same_type_as_parent(): void
    {
        // Arrange: Создаем родителя-Расход
        $parent = Category::factory()->create([
            'type' => CategoryType::Expense
        ]);

        // Assert & Act: Ожидаем ошибку при попытке создать ребенка-Доход
        $this->expectException(CategoryChildTypeException::class);
        $this->expectExceptionMessage('Child category must have the same type as parent.');

        Category::factory()->create([
            'parent_id' => $parent->id,
            'type'      => CategoryType::Income // Пытаемся подсунуть другой тип
        ]);
    }
}
