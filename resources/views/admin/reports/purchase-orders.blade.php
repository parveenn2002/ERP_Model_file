@extends('layouts.admin')

@section('title', 'Purchase Orders Report')
@section('page-title', 'Purchase Orders Report')
@section('icon', 'file-invoice')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-file-invoice me-2"></i> Purchase Orders Report
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Warehouse</th>
                        <th>Status</th>
                        <th>Grand Total</th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrders as $po)
                    <tr>
                        <td>{{ $po->po_number }}</td>
                        <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                        <td>{{ $po->warehouse->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $po->getStatusBadgeColor() }}">
                                {{ $po->getStatusLabel() }}
                            </span>
                        </td>
                        <td>${{ number_format($po->grand_total, 2) }}</td>
                        <td>{{ $po->creator->name ?? 'N/A' }}</td>
                        <td>{{ $po->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $purchaseOrders->links() }}
    </div>
</div>
@endsection