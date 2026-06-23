@extends('layouts.admin')

@section('title', 'Stock Movements Report')
@section('page-title', 'Stock Movements Report')
@section('icon', 'exchange-alt')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-exchange-alt me-2"></i> Stock Movements Report
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Balance After</th>
                        <th>Notes</th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $movement->product->name ?? 'N/A' }}</td>
                        <td>{{ $movement->warehouse->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $movement->type === 'in' ? 'success' : ($movement->type === 'out' ? 'danger' : 'warning') }}">
                                {{ ucfirst($movement->type) }}
                            </span>
                        </td>
                        <td>{{ $movement->quantity }}</td>
                        <td>{{ $movement->balance_after }}</td>
                        <td>{{ $movement->notes }}</td>
                        <td>{{ $movement->creator->name ?? 'System' }}</td>
                        <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $movements->links() }}
    </div>
</div>
@endsection