@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('icon', 'tachometer-alt')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Low Stock Products</h6>
                        <h2 class="mb-0">{{ $stats['low_stock_count'] }}</h2>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
                <small>Products below reorder level</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Pending Orders</h6>
                        <h2 class="mb-0">{{ $stats['pending_orders_count'] }}</h2>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
                <small>Submitted/Approved purchase orders</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Total Stock Value</h6>
                        <h2 class="mb-0">${{ number_format($stats['total_stock_value'], 2) }}</h2>
                    </div>
                    <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                </div>
                <small>Total inventory value</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-invoice me-2"></i> Open Purchase Orders
                <span class="badge bg-primary float-end">{{ $openOrders->count() }}</span>
            </div>
            <div class="card-body">
                @if($openOrders && $openOrders->count() > 0)
                    <div class="list-group">
                        @foreach($openOrders as $order)
                            <a href="{{ route('admin.purchase-orders.show', $order) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $order->po_number }}</strong>
                                        <br>
                                        <small>{{ $order->supplier->name }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $order->getStatusBadgeColor() }} badge-status">
                                            {{ $order->getStatusLabel() }}
                                        </span>
                                        <br>
                                        <small>${{ number_format($order->grand_total, 2) }}</small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">No open purchase orders</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-warehouse me-2"></i> Warehouse Summary
            </div>
            <div class="card-body">
                @if($warehouseSummary && $warehouseSummary->count() > 0)
                    @foreach($warehouseSummary as $data)
                        <div class="mb-3 p-3 border rounded">
                            <h6 class="mb-2">{{ $data['warehouse']->name }}</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-muted small">Products</div>
                                    <strong>{{ $data['summary']['product_count'] }}</strong>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">Units</div>
                                    <strong>{{ $data['summary']['total_units'] }}</strong>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">Value</div>
                                    <strong>${{ number_format($data['summary']['stock_value'], 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3">No warehouse data available</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection