<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\User;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition()
    {
        return [
            'po_number' => 'PO-' . now()->year . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'supplier_id' => Supplier::factory(),
            'warehouse_id' => Warehouse::factory(),
            'status' => PurchaseOrderStatus::DRAFT,
            'order_date' => now(),
            'expected_date' => now()->addDays(7),
            'subtotal' => 0,
            'tax_amount' => 0,
            'grand_total' => 0,
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }
}