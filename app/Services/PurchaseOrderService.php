<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PurchaseOrderService
{
    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'po_number' => $this->generatePONumber(),
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'status' => PurchaseOrderStatus::DRAFT,
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
                'subtotal' => 0,
                'tax_amount' => 0,
                'grand_total' => 0,
            ]);

            $this->updateItems($po, $data['items']);

            return $po;
        });
    }

    public function update(PurchaseOrder $po, array $data): PurchaseOrder
    {
        if (!$po->isEditable()) {
            throw new \Exception('Only draft purchase orders can be edited.');
        }

        return DB::transaction(function () use ($po, $data) {
            $po->update([
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Delete existing items
            $po->items()->delete();

            $this->updateItems($po, $data['items']);

            return $po;
        });
    }

    private function updateItems(PurchaseOrder $po, array $items)
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $lineTotal;

            $po->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $lineTotal,
            ]);
        }

        $taxAmount = $subtotal * 0.05; // 5% tax
        $grandTotal = $subtotal + $taxAmount;

        $po->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
        ]);
    }


    public function submit(PurchaseOrder $po): PurchaseOrder
    {
        if (!$po->canBeSubmitted()) {
            throw new \Exception('Only draft purchase orders can be submitted.');
        }
        $po->update(['status' => PurchaseOrderStatus::SUBMITTED]);
        
        try {
            activity()->performedOn($po)->causedBy(Auth::user())->log('Purchase order submitted');
        } catch (\Exception $e) {
            \Log::warning('Activity log not available: ' . $e->getMessage());
        }
        
        return $po;
    }

    public function approve(PurchaseOrder $po): PurchaseOrder
    {
        if (!$po->canBeApproved()) {
            throw new \Exception('Only submitted purchase orders can be approved.');
        }
        $po->update([
            'status' => PurchaseOrderStatus::APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        
        try {
            activity()->performedOn($po)->causedBy(Auth::user())->log('Purchase order approved');
        } catch (\Exception $e) {
            \Log::warning('Activity log not available: ' . $e->getMessage());
        }
        
        return $po;
    }

    public function receive(PurchaseOrder $po): PurchaseOrder
    {
        if (!$po->canBeReceived()) {
            throw new \Exception('Only approved purchase orders can be received.');
        }

        return DB::transaction(function () use ($po) {
            foreach ($po->items as $item) {
                $this->updateStock($item->product_id, $po->warehouse_id, $item->quantity, $po);
            }

            $po->update([
                'status' => PurchaseOrderStatus::RECEIVED,
                'received_by' => Auth::id(),
                'received_at' => now(),
            ]);

            try {
                activity()->performedOn($po)->causedBy(Auth::user())->log('Purchase order received');
            } catch (\Exception $e) {
                \Log::warning('Activity log not available: ' . $e->getMessage());
            }
            
            return $po;
        });
    }

    public function cancel(PurchaseOrder $po): PurchaseOrder
    {
        if (!$po->canBeCancelled()) {
            throw new \Exception('This purchase order cannot be cancelled.');
        }
        $po->update(['status' => PurchaseOrderStatus::CANCELLED]);
        
        try {
            activity()->performedOn($po)->causedBy(Auth::user())->log('Purchase order cancelled');
        } catch (\Exception $e) {
            \Log::warning('Activity log not available: ' . $e->getMessage());
        }
        
        return $po;
    }

    private function updateStock($productId, $warehouseId, $quantity)
    {
        $stock = WarehouseStock::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId
        ])->first();

        if ($stock) {
            $stock->increment('quantity', $quantity);
            $newBalance = $stock->quantity;
        } else {
            $stock = WarehouseStock::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
            ]);
            $newBalance = $quantity;
        }

        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'type' => 'in',
            'quantity' => $quantity,
            'balance_after' => $newBalance,
            'reference_type' => PurchaseOrder::class,
            'reference_id' => $po->id,
            'notes' => 'Received from purchase order ' . $po->po_number,
            'created_by' => Auth::id(),
        ]);
    }

    private function generatePONumber(): string
    {
        $year = now()->year;
        $lastPo = PurchaseOrder::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPo) {
            $lastNumber = intval(substr($lastPo->po_number, -4));
            $sequence = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return 'PO-' . $year . '-' . $sequence;
    }
}