<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
        // Remove middleware from constructor
    }

    public function index()
    {
        if (request()->ajax()) {
            $stocks = \App\Models\WarehouseStock::with(['product', 'warehouse'])->get();
            
            return datatables()->of($stocks)
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
        
        return view('admin.stock.index');
    }

    public function adjust()
    {
        $products = Product::where('is_active', true)->pluck('name', 'id');
        $warehouses = Warehouse::where('is_active', true)->pluck('name', 'id');
        return view('admin.stock.adjust', compact('products', 'warehouses'));
    }

    public function adjustStore(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'required|string|min:3',
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $warehouse = Warehouse::findOrFail($request->warehouse_id);
            
            $this->stockService->adjust(
                $product,
                $warehouse,
                $request->type,
                $request->quantity,
                $request->notes
            );
            
            return redirect()->route('admin.stock.index')
                ->with('success', 'Stock adjusted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function getStockByProduct(Product $product)
    {
        $stocks = $product->warehouseStocks()->with('warehouse')->get();
        return response()->json($stocks);
    }

    public function getStockByWarehouse(Warehouse $warehouse)
    {
        $stocks = $warehouse->stocks()->with('product')->get();
        return response()->json($stocks);
    }
}