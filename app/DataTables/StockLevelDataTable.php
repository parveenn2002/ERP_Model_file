<?php

namespace App\DataTables;

use App\Models\WarehouseStock;
use Yajra\DataTables\Facades\DataTables;

class StockLevelDataTable
{
    public function dataTable($query)
    {
        return DataTables::of($query)
            ->addColumn('product_name', function ($stock) {
                return $stock->product->name;
            })
            ->addColumn('product_sku', function ($stock) {
                return $stock->product->sku;
            })
            ->addColumn('reorder_level', function ($stock) {
                return $stock->product->reorder_level;
            })
            ->addColumn('warehouse_name', function ($stock) {
                return $stock->warehouse->name;
            })
            ->addColumn('status', function ($stock) {
                $isLowStock = $stock->quantity <= $stock->product->reorder_level;
                return '<span class="badge bg-'.($isLowStock ? 'danger' : 'success').'">
                    '.($isLowStock ? 'Low Stock' : 'In Stock').'
                </span>';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    public function query()
    {
        return WarehouseStock::with(['product', 'warehouse']);
    }
}