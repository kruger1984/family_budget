<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'type' => $this->faker->randomElement(CategoryType::cases()),
            'icon' => $this->faker->imageUrl(width: 150, height: 150),
            'color' => $this->faker->hexColor(),
            'parent_id' => null,
            'family_id' => null,
        ];
    }
}
