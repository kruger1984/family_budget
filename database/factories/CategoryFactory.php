<?php

declare(strict_types=1);

namespace Database\Factories;

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
        $icons = [
            'heroicon-o-shopping-cart',
            'heroicon-o-credit-card',
            'heroicon-o-truck',
            'heroicon-o-home',
            'heroicon-o-academic-cap',
            'heroicon-o-cog-6-tooth',
        ];

        return [
            'name' => $this->faker->word(),
            'icon' => $this->faker->randomElement($icons),
            'color' => $this->faker->safeHexColor(),
            'parent_id' => null,
            'family_id' => null,
        ];
    }
}
