<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\User;
use App\Enums\PurchaseOrderStatus;

class PurchaseOrderSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $products = Product::all();

        // Create purchase orders with different statuses
        $statuses = [
            PurchaseOrderStatus::DRAFT,
            PurchaseOrderStatus::SUBMITTED,
            PurchaseOrderStatus::APPROVED,
            PurchaseOrderStatus::RECEIVED,
            PurchaseOrderStatus::CANCELLED,
        ];

        foreach ($statuses as $index => $status) {
            $po = PurchaseOrder::create([
                'po_number' => 'PO-2024-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'supplier_id' => rand(1, 5),
                'warehouse_id' => rand(1, 3),
                'status' => $status,
                'order_date' => now()->subDays(rand(1, 30)),
                'expected_date' => now()->addDays(rand(5, 15)),
                'notes' => "Sample purchase order - {$status->label()}",
                'created_by' => $users->random()->id,
                'subtotal' => 0,
                'tax_amount' => 0,
                'grand_total' => 0,
            ]);

            // Add items
            $subtotal = 0;
            $items = $products->random(rand(2, 4));
            foreach ($items as $product) {
                $quantity = rand(5, 20);
                $unitPrice = $product->cost_price;
                $lineTotal = $quantity * $unitPrice;
                $subtotal += $lineTotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $taxAmount = $subtotal * 0.05;
            $grandTotal = $subtotal + $taxAmount;

            $po->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
            ]);

            // Set additional fields based on status
            if ($status === PurchaseOrderStatus::APPROVED || $status === PurchaseOrderStatus::RECEIVED) {
                $po->update([
                    'approved_by' => $users->random()->id,
                    'approved_at' => now()->subDays(rand(1, 5)),
                ]);
            }

            if ($status === PurchaseOrderStatus::RECEIVED) {
                $po->update([
                    'received_by' => $users->random()->id,
                    'received_at' => now()->subDays(rand(1, 3)),
                ]);
            }
        }
    }
}