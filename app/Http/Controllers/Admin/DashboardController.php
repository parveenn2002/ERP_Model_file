<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InventoryReportService;

class DashboardController extends Controller
{
    public function index(InventoryReportService $reportService)
    {
        $stats = $reportService->getDashboardStats();
        $openOrders = $reportService->getOpenPurchaseOrders();
        $warehouseSummary = $reportService->getWarehouseStockSummary();

        return view('admin.dashboard.index', compact('stats', 'openOrders', 'warehouseSummary'));
    }
}