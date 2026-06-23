<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect('/login');
});

// Login Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended('/admin');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);
    
    // Products
    Route::resource('products', ProductController::class)->except(['show']);
    
    // Suppliers
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    
    // Warehouses
    Route::resource('warehouses', WarehouseController::class)->except(['show']);
    
    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])
        ->name('purchase-orders.submit');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
        ->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
        ->name('purchase-orders.receive');
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
        ->name('purchase-orders.cancel');
    
    // Stock Management
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    Route::post('stock/adjust', [StockController::class, 'adjustStore'])->name('stock.adjust.store');
    
    // Report Routes - Using can middleware instead of permission
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('purchase-orders', [ReportController::class, 'purchaseOrders'])->name('purchase-orders');
        Route::get('stock-movements', [ReportController::class, 'stockMovements'])->name('stock-movements');
    });
});