@extends('layouts.admin')

@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')
@section('icon', 'box')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-box me-2"></i> Inventory Report
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>SKU</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Cost Price</th>
                        <th>Total Stock</th>
                        <th>Total Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                        <td>${{ number_format($product->cost_price, 2) }}</td>
                        <td>{{ $product->total_stock }}</td>
                        <td>${{ number_format($product->total_stock * $product->cost_price, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $product->is_active ? 'success' : 'danger' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
    </div>
</div>
@endsection