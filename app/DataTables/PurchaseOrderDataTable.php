<?php

namespace App\DataTables;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PurchaseOrderDataTable
{
    public function dataTable($query)
    {
        return DataTables::of($query)
            ->addColumn('action', function ($po) {
                $actions = '';
                if (Auth::user()->can('purchase_orders.create')) {
                    $actions .= '<a href="'.route('admin.purchase-orders.show', $po).'" class="btn btn-sm btn-info me-1">
                        <i class="fas fa-eye"></i>
                    </a>';
                    if ($po->isEditable()) {
                        $actions .= '<a href="'.route('admin.purchase-orders.edit', $po).'" class="btn btn-sm btn-warning me-1">
                            <i class="fas fa-edit"></i>
                        </a>';
                    }
                }
                return $actions;
            })
            ->addColumn('status', function ($po) {
                return '<span class="badge bg-'.$po->status->badgeColor().'">
                    '.$po->status->label().'
                </span>';
            })
            ->editColumn('grand_total', function ($po) {
                return '$'.number_format($po->grand_total, 2);
            })
            ->editColumn('order_date', function ($po) {
                return $po->order_date->format('Y-m-d');
            })
            ->addColumn('supplier_name', function ($po) {
                return $po->supplier->name;
            })
            ->addColumn('warehouse_name', function ($po) {
                return $po->warehouse->name;
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function query()
    {
        return PurchaseOrder::with(['supplier', 'warehouse']);
    }
}