<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Meals', 'slug' => 'meals', 'sort_order' => 1],
            ['name' => 'Snacks', 'slug' => 'snacks', 'sort_order' => 2],
            ['name' => 'Beverages', 'slug' => 'beverages', 'sort_order' => 3],
            ['name' => 'Desserts', 'slug' => 'desserts', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
