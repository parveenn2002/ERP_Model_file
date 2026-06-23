@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products')
@section('icon', 'box')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Product List</span>
        @can('products.manage')
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Product
        </a>
        @endcan
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="products-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Cost Price</th>
                    <th>Total Stock</th>
                    <th>Status</th>
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
    $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.products.index') }}",
        columns: [
            { data: 'sku', name: 'sku' },
            { data: 'name', name: 'name' },
            { data: 'category_name', name: 'category.name' },
            { data: 'cost_price', name: 'cost_price' },
            { data: 'total_stock', name: 'total_stock' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        responsive: true,
        order: [[1, 'asc']]
    });
});
</script>
@endpush