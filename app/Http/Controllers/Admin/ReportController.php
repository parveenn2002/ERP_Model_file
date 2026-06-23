<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use App\Models\WarehouseStock;
use App\Services\InventoryReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(InventoryReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        // Check permission manually
        if (!Auth::user()->can('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        // Get all data for reports
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();
        $totalWarehouses = Warehouse::count();
        
        $totalPurchaseOrders = PurchaseOrder::count();
        $pendingOrders = PurchaseOrder::whereIn('status', ['submitted', 'approved'])->count();
        $receivedOrders = PurchaseOrder::where('status', 'received')->count();
        $cancelledOrders = PurchaseOrder::where('status', 'cancelled')->count();
        
        $lowStockProducts = $this->reportService->getLowStockCount();
        $totalStockValue = $this->reportService->getTotalStockValue();
        
        // Get purchase orders by month
        $ordersByMonth = PurchaseOrder::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();
        
        // Get top products by stock value
        $topProducts = WarehouseStock::with('product')
            ->selectRaw('product_id, SUM(quantity * (SELECT cost_price FROM products WHERE products.id = warehouse_stock.product_id)) as total_value')
            ->groupBy('product_id')
            ->orderBy('total_value', 'desc')
            ->limit(10)
            ->get();
        
        // Get stock by warehouse
        $stockByWarehouse = Warehouse::with('stocks')
            ->get()
            ->map(function ($warehouse) {
                return [
                    'name' => $warehouse->name,
                    'total_units' => $warehouse->stocks->sum('quantity'),
                    'total_value' => $warehouse->stocks->sum(function ($stock) {
                        return $stock->quantity * $stock->product->cost_price;
                    })
                ];
            });
        
        // Get recent activities
        $recentActivities = collect();
        try {
            if (class_exists('Spatie\Activitylog\ActivitylogServiceProvider')) {
                $recentActivities = \Spatie\Activitylog\Models\Activity::with('causer')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
            }
        } catch (\Exception $e) {
            $recentActivities = collect();
        }

        return view('admin.reports.index', compact(
            'totalProducts',
            'activeProducts',
            'totalCategories',
            'totalSuppliers',
            'totalWarehouses',
            'totalPurchaseOrders',
            'pendingOrders',
            'receivedOrders',
            'cancelledOrders',
            'lowStockProducts',
            'totalStockValue',
            'ordersByMonth',
            'topProducts',
            'stockByWarehouse',
            'recentActivities'
        ));
    }

    public function inventory()
    {
        if (!Auth::user()->can('reports.view')) {
            abort(403, 'Unauthorized action.');
        }
        $products = Product::with(['category', 'warehouseStocks'])->paginate(20);
        return view('admin.reports.inventory', compact('products'));
    }

    public function purchaseOrders()
    {
        if (!Auth::user()->can('reports.view')) {
            abort(403, 'Unauthorized action.');
        }
        $purchaseOrders = PurchaseOrder::with(['supplier', 'warehouse', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.reports.purchase-orders', compact('purchaseOrders'));
    }

    public function stockMovements()
    {
        if (!Auth::user()->can('reports.view')) {
            abort(403, 'Unauthorized action.');
        }
        $movements = \App\Models\StockMovement::with(['product', 'warehouse', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.reports.stock-movements', compact('movements'));
    }
}