@extends('layouts.admin')

@section('title', 'Stock Management')
@section('page-title', 'Stock Management')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Stock Levels</h5>
        @can('stock.adjust')
        <a href="{{ route('admin.stock.adjust') }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Adjust Stock
        </a>
        @endcan
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="stock-table">
            <thead>
                <tr>
                    <th>Warehouse</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Quantity</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#stock-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.stock.index') }}",
        columns: [
            { data: 'warehouse_name', name: 'warehouse.name' },
            { data: 'product_name', name: 'product.name' },
            { data: 'product_sku', name: 'product.sku' },
            { data: 'quantity', name: 'quantity' },
            { data: 'reorder_level', name: 'product.reorder_level' },
            { data: 'status', name: 'status' }
        ]
    });
});
</script>
@endpush