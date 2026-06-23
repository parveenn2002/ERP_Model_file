@extends('layouts.admin')

@section('title', 'Adjust Stock')
@section('page-title', 'Adjust Stock')
@section('icon', 'edit')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i> Manual Stock Adjustment
    </div>
    <div class="card-body">
        <form action="{{ route('admin.stock.adjust.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select @error('product_id') is-invalid @enderror" 
                                id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            @foreach($products as $id => $name)
                                <option value="{{ $id }}" {{ old('product_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                        <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                id="warehouse_id" name="warehouse_id" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $id => $name)
                                <option value="{{ $id }}" {{ old('warehouse_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="type" class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>Stock In</option>
                            <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                            <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" name="quantity" min="1" value="{{ old('quantity') }}" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Reason/Notes <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('notes') is-invalid @enderror" 
                               id="notes" name="notes" value="{{ old('notes') }}" required>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Note:</strong> 
                <ul class="mb-0 mt-1">
                    <li><strong>Stock In:</strong> Adds quantity to the selected warehouse</li>
                    <li><strong>Stock Out:</strong> Removes quantity from the selected warehouse</li>
                    <li><strong>Adjustment:</strong> Sets the quantity to the specified value</li>
                </ul>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Adjust Stock
                </button>
                <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection