<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Warehouse;
use App\Enums\PurchaseOrderStatus;

class InventoryReportService
{
    public function getDashboardStats()
    {
        return [
            'low_stock_count' => $this->getLowStockCount(),
            'pending_orders_count' => $this->getPendingOrdersCount(),
            'total_stock_value' => app(StockService::class)->getTotalStockValue(),
        ];
    }

    public function getLowStockCount()
    {
        return Product::active()
            ->whereHas('warehouseStocks', function ($query) {
                $query->havingRaw('SUM(quantity) <= products.reorder_level');
            })
            ->count();
    }

    public function getPendingOrdersCount()
    {
        return PurchaseOrder::whereIn('status', [
            PurchaseOrderStatus::SUBMITTED,
            PurchaseOrderStatus::APPROVED
        ])->count();
    }

    public function getOpenPurchaseOrders()
    {
        return PurchaseOrder::with(['supplier', 'warehouse'])
            ->whereIn('status', [
                PurchaseOrderStatus::SUBMITTED,
                PurchaseOrderStatus::APPROVED
            ])
            ->orderBy('order_date', 'desc')
            ->get();
    }

    public function getWarehouseStockSummary()
    {
        return Warehouse::with(['stocks.product.category'])
            ->get()
            ->map(function ($warehouse) {
                $summary = app(StockService::class)->getWarehouseSummary($warehouse);
                return [
                    'warehouse' => $warehouse,
                    'summary' => $summary,
                ];
            });
    }

    public function getTotalStockValue()
    {
        return app(StockService::class)->getTotalStockValue();
    }
}