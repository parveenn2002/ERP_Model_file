<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'category_id' => Category::factory(),
            'sku' => $this->faker->unique()->ean8(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'box', 'pack']),
            'cost_price' => $this->faker->randomFloat(2, 10, 1000),
            'reorder_level' => $this->faker->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}