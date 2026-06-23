<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PurchaseOrderService
{
    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $purchaseOrder = PurchaseOrder::create([
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

            $this->updateItems($purchaseOrder, $data['items']);
            $this->logActivity($purchaseOrder, 'Purchase order created in draft status');
            
            return $purchaseOrder;
        });
    }

    public function update(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        if (!$purchaseOrder->canBeEdited()) {
            throw new \Exception('Only draft purchase orders can be edited.');
        }

        return DB::transaction(function () use ($purchaseOrder, $data) {
            $purchaseOrder->update([
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $purchaseOrder->items()->delete();
            $this->updateItems($purchaseOrder, $data['items']);
            $this->logActivity($purchaseOrder, 'Purchase order updated');
            
            return $purchaseOrder;
        });
    }

    private function updateItems(PurchaseOrder $purchaseOrder, array $items)
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $lineTotal;
            $purchaseOrder->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
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
    }

    public function submit(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if (!$purchaseOrder->canBeSubmitted()) {
            throw new \Exception('Only draft purchase orders can be submitted.');
        }

        $purchaseOrder->markAsSubmitted();
        $this->logActivity($purchaseOrder, 'Purchase order submitted for approval');
        
        return $purchaseOrder;
    }

    public function approve(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if (!$purchaseOrder->canBeApproved()) {
            throw new \Exception('Only submitted purchase orders can be approved.');
        }

        $purchaseOrder->markAsApproved(Auth::id());
        $this->logActivity($purchaseOrder, 'Purchase order approved by ' . Auth::user()->name);
        
        return $purchaseOrder;
    }

    public function receive(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if (!$purchaseOrder->canBeReceived()) {
            throw new \Exception('Only approved purchase orders can be received.');
        }

        return DB::transaction(function () use ($purchaseOrder) {
            foreach ($purchaseOrder->items as $item) {
                $this->updateStock($item->product_id, $purchaseOrder->warehouse_id, $item->quantity, $purchaseOrder);
            }

            $purchaseOrder->markAsReceived(Auth::id());
            $this->logActivity($purchaseOrder, 'Purchase order received and stock updated by ' . Auth::user()->name);
            
            return $purchaseOrder;
        });
    }

    public function cancel(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if (!$purchaseOrder->canBeCancelled()) {
            throw new \Exception('This purchase order cannot be cancelled. Only draft, submitted, or approved orders can be cancelled.');
        }

        $purchaseOrder->markAsCancelled();
        $this->logActivity($purchaseOrder, 'Purchase order cancelled by ' . Auth::user()->name);
        
        return $purchaseOrder;
    }

    private function updateStock($productId, $warehouseId, $quantity, $purchaseOrder)
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
            'reference_id' => $purchaseOrder->id,
            'notes' => 'Received from purchase order ' . $purchaseOrder->po_number,
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

    private function logActivity($purchaseOrder, string $description): void
    {
        try {
            if (class_exists('Spatie\Activitylog\ActivitylogServiceProvider')) {
                activity()
                    ->performedOn($purchaseOrder)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'status' => $purchaseOrder->status->value,
                        'po_number' => $purchaseOrder->po_number
                    ])
                    ->log($description);
            }
        } catch (\Exception $e) {
            Log::warning('Activity log not available: ' . $e->getMessage());
        }
    }
}