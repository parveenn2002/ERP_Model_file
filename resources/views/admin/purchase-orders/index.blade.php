@extends('layouts.admin')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')
@section('icon', 'file-invoice')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Purchase Order List</span>
        @can('purchase_orders.create')
        <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Create PO
        </a>
        @endcan
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="purchase-orders-table">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Warehouse</th>
                    <th>Status</th>
                    <th>Grand Total</th>
                    <th>Order Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#purchase-orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.purchase-orders.index') }}",
        columns: [
            { data: 'po_number', name: 'po_number' },
            { data: 'supplier_name', name: 'supplier.name' },
            { data: 'warehouse_name', name: 'warehouse.name' },
            { data: 'status', name: 'status' },
            { data: 'grand_total', name: 'grand_total' },
            { data: 'order_date', name: 'order_date' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        responsive: true,
        order: [[5, 'desc']]
    });
});
</script>
@endpush