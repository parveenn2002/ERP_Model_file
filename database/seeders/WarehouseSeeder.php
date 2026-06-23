<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run()
    {
        $warehouses = [
            ['code' => 'WH001', 'name' => 'Main Warehouse', 'location' => '123 Main St, City Center'],
            ['code' => 'WH002', 'name' => 'East Warehouse', 'location' => '456 East St, Industrial Area'],
            ['code' => 'WH003', 'name' => 'West Warehouse', 'location' => '789 West St, Commercial Zone'],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}