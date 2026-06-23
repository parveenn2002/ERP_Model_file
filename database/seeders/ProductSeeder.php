<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $categoryIds = Category::pluck('id')->toArray();
        $warehouseIds = Warehouse::pluck('id')->toArray();

        $products = [
            ['name' => 'Laptop Pro', 'sku' => 'LAP001', 'unit' => 'pcs', 'cost_price' => 899.99, 'reorder_level' => 5],
            ['name' => 'Wireless Mouse', 'sku' => 'MOU001', 'unit' => 'pcs', 'cost_price' => 29.99, 'reorder_level' => 20],
            ['name' => 'Office Chair', 'sku' => 'CHA001', 'unit' => 'pcs', 'cost_price' => 149.99, 'reorder_level' => 8],
            ['name' => 'Coffee Beans', 'sku' => 'COF001', 'unit' => 'kg', 'cost_price' => 15.99, 'reorder_level' => 30],
            ['name' => 'Notebook Pack', 'sku' => 'NOT001', 'unit' => 'pack', 'cost_price' => 9.99, 'reorder_level' => 50],
            ['name' => 'Smartphone', 'sku' => 'PHO001', 'unit' => 'pcs', 'cost_price' => 699.99, 'reorder_level' => 10],
            ['name' => 'Desk Lamp', 'sku' => 'LAM001', 'unit' => 'pcs', 'cost_price' => 39.99, 'reorder_level' => 15],
            ['name' => 'Water Bottle', 'sku' => 'BOT001', 'unit' => 'pcs', 'cost_price' => 12.99, 'reorder_level' => 25],
        ];

        foreach ($products as $index => $productData) {
            $categoryId = $categoryIds[$index % count($categoryIds)];
            $product = Product::create([
                'category_id' => $categoryId,
                'sku' => $productData['sku'],
                'name' => $productData['name'],
                'description' => "High quality {$productData['name']}",
                'unit' => $productData['unit'],
                'cost_price' => $productData['cost_price'],
                'reorder_level' => $productData['reorder_level'],
                'is_active' => true,
            ]);

            // Create stock for each warehouse
            foreach ($warehouseIds as $warehouseId) {
                WarehouseStock::create([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $product->id,
                    'quantity' => rand(10, 100),
                ]);
            }
        }
    }
}