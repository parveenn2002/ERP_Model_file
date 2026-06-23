@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports Dashboard')
@section('icon', 'chart-bar')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Total Products</h6>
                <h2 class="mb-0">{{ $totalProducts }}</h2>
                <small>{{ $activeProducts }} active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Total Categories</h6>
                <h2 class="mb-0">{{ $totalCategories }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Total Suppliers</h6>
                <h2 class="mb-0">{{ $totalSuppliers }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Total Warehouses</h6>
                <h2 class="mb-0">{{ $totalWarehouses }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Total POs</h6>
                <h2 class="mb-0">{{ $totalPurchaseOrders }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Pending POs</h6>
                <h2 class="mb-0">{{ $pendingOrders }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Received POs</h6>
                <h2 class="mb-0">{{ $receivedOrders }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Cancelled POs</h6>
                <h2 class="mb-0">{{ $cancelledOrders }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i> Stock Summary
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <h6>Low Stock Products</h6>
                        <h3 class="text-warning">{{ $lowStockProducts }}</h3>
                    </div>
                    <div class="col-6">
                        <h6>Total Stock Value</h6>
                        <h3 class="text-success">${{ number_format($totalStockValue, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-warehouse me-2"></i> Stock by Warehouse
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Warehouse</th>
                                <th>Units</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockByWarehouse as $warehouse)
                            <tr>
                                <td>{{ $warehouse['name'] }}</td>
                                <td>{{ $warehouse['total_units'] }}</td>
                                <td>${{ number_format($warehouse['total_value'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-crown me-2"></i> Top Products by Value
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $item)
                            <tr>
                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                <td>${{ number_format($item->total_value, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Recent Activities
            </div>
            <div class="card-body">
                @if($recentActivities && $recentActivities->count() > 0)
                    <div class="list-group">
                        @foreach($recentActivities as $activity)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span>{{ $activity->description }}</span>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                                <small>By: {{ $activity->causer ? $activity->causer->name : 'System' }}</small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">No recent activities</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-invoice me-2"></i> Purchase Orders by Month
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Month</th>
                                <th>Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordersByMonth as $order)
                            <tr>
                                <td>{{ $order->year }}</td>
                                <td>{{ date('F', mktime(0, 0, 0, $order->month, 1)) }}</td>
                                <td>{{ $order->count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <a href="{{ route('admin.reports.inventory') }}" class="text-decoration-none">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <i class="fas fa-box fa-3x text-primary"></i>
                    <h5 class="mt-2">Inventory Report</h5>
                    <small>View all products with stock details</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.reports.purchase-orders') }}" class="text-decoration-none">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <i class="fas fa-file-invoice fa-3x text-success"></i>
                    <h5 class="mt-2">Purchase Orders Report</h5>
                    <small>View all purchase orders</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.reports.stock-movements') }}" class="text-decoration-none">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <i class="fas fa-exchange-alt fa-3x text-warning"></i>
                    <h5 class="mt-2">Stock Movements Report</h5>
                    <small>View all stock movements</small>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection