<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $meals = Category::where('slug', 'meals')->first();
        $snacks = Category::where('slug', 'snacks')->first();
        $beverages = Category::where('slug', 'beverages')->first();

        $products = [
            // Meals
            ['category_id' => $meals->id, 'name' => 'Chicken Adobo with Rice', 'slug' => 'chicken-adobo-with-rice', 'price' => 75.00, 'stock' => 50],
            ['category_id' => $meals->id, 'name' => 'Pancit Canton', 'slug' => 'pancit-canton', 'price' => 60.00, 'stock' => 50],
            ['category_id' => $meals->id, 'name' => 'Sari-Sari (Shanghai Blend)', 'slug' => 'sari-sari-shanghai-blend', 'price' => 65.00, 'stock' => 30],

            // Snacks
            ['category_id' => $snacks->id, 'name' => 'Lumpia Shanghai (5pcs)', 'slug' => 'lumpia-shanghai-5pcs', 'price' => 35.00, 'stock' => 80],
            ['category_id' => $snacks->id, 'name' => 'Burger', 'slug' => 'burger', 'price' => 45.00, 'stock' => 40],
            ['category_id' => $snacks->id, 'name' => 'French Fries', 'slug' => 'french-fries', 'price' => 40.00, 'stock' => 60],

            // Beverages
            ['category_id' => $beverages->id, 'name' => 'Iced Coffee', 'slug' => 'iced-coffee', 'price' => 35.00, 'stock' => 100],
            ['category_id' => $beverages->id, 'name' => 'Fresh Mango Juice', 'slug' => 'fresh-mango-juice', 'price' => 30.00, 'stock' => 60],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
