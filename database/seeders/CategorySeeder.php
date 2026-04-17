<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Зарплата', 'icon' => 'heroicon-o-briefcase', 'color' => '#10B981'],
            ['name' => 'Продукты', 'icon' => 'heroicon-o-shopping-cart', 'color' => '#EF4444'],
            ['name' => 'Транспорт', 'icon' => 'heroicon-o-truck', 'color' => '#3B82F6'],
            ['name' => 'Аренда', 'icon' => 'heroicon-o-home', 'color' => '#F59E0B'],
        ];

        foreach ($categories as $category) {
            Category::query()->create($category);
        }
    }
}
