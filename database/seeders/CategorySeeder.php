<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and accessories'],
            ['name' => 'Clothing', 'description' => 'Apparel and fashion items'],
            ['name' => 'Furniture', 'description' => 'Home and office furniture'],
            ['name' => 'Food & Beverages', 'description' => 'Food products and beverages'],
            ['name' => 'Office Supplies', 'description' => 'Office stationery and supplies'],
            ['name' => 'Books & Stationery', 'description' => 'Books, notebooks, and stationery items'],
            ['name' => 'Health & Beauty', 'description' => 'Health care and beauty products'],
            ['name' => 'Sports & Outdoors', 'description' => 'Sports equipment and outdoor gear'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_active' => true,
            ]);
            echo "✅ Category created: " . $category['name'] . "\n";
        }
    }
}