<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $suppliers = [
            ['code' => 'SUP001', 'name' => 'TechMart Inc.', 'email' => 'info@techmart.com', 'phone' => '123-456-7890', 'address' => '123 Tech Street, Silicon Valley'],
            ['code' => 'SUP002', 'name' => 'FashionHub Ltd.', 'email' => 'contact@fashionhub.com', 'phone' => '234-567-8901', 'address' => '456 Fashion Avenue, New York'],
            ['code' => 'SUP003', 'name' => 'Furniture World', 'email' => 'sales@furnitureworld.com', 'phone' => '345-678-9012', 'address' => '789 Furniture Blvd, Chicago'],
            ['code' => 'SUP004', 'name' => 'FoodExpress', 'email' => 'orders@foodexpress.com', 'phone' => '456-789-0123', 'address' => '101 Food Street, Los Angeles'],
            ['code' => 'SUP005', 'name' => 'OfficePro', 'email' => 'info@officepro.com', 'phone' => '567-890-1234', 'address' => '202 Office Road, Dallas'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}