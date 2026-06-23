<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockService
{
    public function adjust(Product $product, Warehouse $warehouse, string $type, int $quantity, string $notes = null)
    {
        return DB::transaction(function () use ($product, $warehouse, $type, $quantity, $notes) {
            $stock = WarehouseStock::where([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id
            ])->first();

            if ($type === 'out' || $type === 'adjustment') {
                $currentQty = $stock ? $stock->quantity : 0;
                $newQty = $type === 'out' ? $currentQty - $quantity : $quantity;
                if ($newQty < 0) {
                    throw new \Exception('Stock quantity cannot be negative.');
                }
            }

            if ($stock) {
                if ($type === 'in') {
                    $stock->increment('quantity', $quantity);
                    $newBalance = $stock->quantity;
                } elseif ($type === 'out') {
                    $stock->decrement('quantity', $quantity);
                    $newBalance = $stock->quantity;
                } else {
                    $stock->update(['quantity' => $quantity]);
                    $newBalance = $quantity;
                }
            } else {
                if ($type === 'out') {
                    throw new \Exception('Cannot remove stock from non-existent record.');
                }
                $stock = WarehouseStock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $type === 'adjustment' ? $quantity : $quantity,
                ]);
                $newBalance = $stock->quantity;
            }

            // Create stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => $type,
                'quantity' => $quantity,
                'balance_after' => $newBalance,
                'reference_type' => 'manual',
                'reference_id' => null,
                'notes' => $notes ?? 'Manual stock adjustment',
                'created_by' => Auth::id(),
            ]);

            // Try to log activity, but don't fail if activity log table doesn't exist
            try {
                if (class_exists('Spatie\Activitylog\ActivitylogServiceProvider')) {
                    activity()
                        ->performedOn($stock)
                        ->causedBy(Auth::user())
                        ->withProperties(['type' => $type, 'quantity' => $quantity, 'new_balance' => $newBalance])
                        ->log('Manual stock adjustment');
                }
            } catch (\Exception $e) {
                // Activity log table doesn't exist, skip logging
                // Log the error for debugging
                \Log::warning('Activity log not available: ' . $e->getMessage());
            }

            return $stock;
        });
    }

    public function getLowStockProducts()
    {
        return Product::active()
            ->with(['warehouseStocks', 'category'])
            ->get()
            ->filter(function ($product) {
                return $product->total_stock <= $product->reorder_level;
            });
    }

    public function getWarehouseSummary(Warehouse $warehouse)
    {
        $stocks = $warehouse->stocks()
            ->with('product')
            ->where('quantity', '>', 0)
            ->get();

        return [
            'product_count' => $stocks->count(),
            'total_units' => $stocks->sum('quantity'),
            'stock_value' => $stocks->sum(function ($stock) {
                return $stock->quantity * $stock->product->cost_price;
            }),
        ];
    }

    public function getTotalStockValue()
    {
        return WarehouseStock::with('product')
            ->get()
            ->sum(function ($stock) {
                return $stock->quantity * $stock->product->cost_price;
            });
    }
}