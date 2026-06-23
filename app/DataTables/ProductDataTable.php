<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProductDataTable
{
    public function dataTable($query)
    {
        return DataTables::of($query)
            ->addColumn('action', function ($product) {
                $actions = '';
                if (Auth::user()->can('products.manage')) {
                    $actions .= '<a href="'.route('admin.products.edit', $product).'" class="btn btn-sm btn-warning me-1">
                        <i class="fas fa-edit"></i>
                    </a>';
                    $actions .= '<form action="'.route('admin.products.destroy', $product).'" method="POST" class="d-inline">
                        '.csrf_field().'
                        '.method_field('DELETE').'
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>';
                }
                return $actions;
            })
            ->addColumn('status', function ($product) {
                return '<span class="badge bg-'.($product->is_active ? 'success' : 'danger').'">
                    '.($product->is_active ? 'Active' : 'Inactive').'
                </span>';
            })
            ->addColumn('total_stock', function ($product) {
                return $product->total_stock;
            })
            ->addColumn('category_name', function ($product) {
                return $product->category ? $product->category->name : 'N/A';
            })
            ->editColumn('cost_price', function ($product) {
                return '$'.number_format($product->cost_price, 2);
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function query()
    {
        return Product::with(['category', 'warehouseStocks']);
    }

    public function html()
    {
        return view('admin.products.index', ['dataTable' => $this]);
    }
}