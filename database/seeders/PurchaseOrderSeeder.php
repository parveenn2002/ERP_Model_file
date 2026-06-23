<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Enums\PurchaseOrderStatus;

class PurchaseOrderSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $products = Product::all();
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();

        if ($users->isEmpty() || $products->isEmpty() || $suppliers->isEmpty() || $warehouses->isEmpty()) {
            $this->command->warn('Please run other seeders first.');
            return;
        }

        // Create purchase orders with different statuses
        $statuses = [
            PurchaseOrderStatus::DRAFT,
            PurchaseOrderStatus::SUBMITTED,
            PurchaseOrderStatus::APPROVED,
            PurchaseOrderStatus::RECEIVED,
            PurchaseOrderStatus::CANCELLED,
        ];

        foreach ($statuses as $index => $status) {
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => 'PO-2026-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'supplier_id' => $suppliers->random()->id,
                'warehouse_id' => $warehouses->random()->id,
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
            $selectedProducts = $products->random(rand(2, 4));
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(5, 20);
                $unitPrice = $product->cost_price;
                $lineTotal = $quantity * $unitPrice;
                $subtotal += $lineTotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $taxAmount = $subtotal * 0.05;
            $grandTotal = $subtotal + $taxAmount;

            $purchaseOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
            ]);

            // Set additional fields based on status
            if ($status === PurchaseOrderStatus::APPROVED || $status === PurchaseOrderStatus::RECEIVED) {
                $purchaseOrder->update([
                    'approved_by' => $users->random()->id,
                    'approved_at' => now()->subDays(rand(1, 5)),
                ]);
            }

            if ($status === PurchaseOrderStatus::RECEIVED) {
                $purchaseOrder->update([
                    'received_by' => $users->random()->id,
                    'received_at' => now()->subDays(rand(1, 3)),
                ]);
            }
        }

        // Create additional SUBMITTED and APPROVED orders for dashboard
        for ($i = 0; $i < 3; $i++) {
            $status = $i % 2 == 0 ? PurchaseOrderStatus::SUBMITTED : PurchaseOrderStatus::APPROVED;
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => 'PO-2026-' . str_pad($i + 10, 4, '0', STR_PAD_LEFT),
                'supplier_id' => $suppliers->random()->id,
                'warehouse_id' => $warehouses->random()->id,
                'status' => $status,
                'order_date' => now()->subDays(rand(1, 10)),
                'expected_date' => now()->addDays(rand(3, 10)),
                'notes' => "Open purchase order - {$status->label()}",
                'created_by' => $users->random()->id,
                'subtotal' => 0,
                'tax_amount' => 0,
                'grand_total' => 0,
            ]);

            $subtotal = 0;
            $selectedProducts = $products->random(rand(2, 3));
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(3, 10);
                $unitPrice = $product->cost_price;
                $lineTotal = $quantity * $unitPrice;
                $subtotal += $lineTotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $taxAmount = $subtotal * 0.05;
            $grandTotal = $subtotal + $taxAmount;

            $purchaseOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
            ]);

            if ($status === PurchaseOrderStatus::APPROVED) {
                $purchaseOrder->update([
                    'approved_by' => $users->random()->id,
                    'approved_at' => now()->subDays(rand(1, 3)),
                ]);
            }
        }
    }
}