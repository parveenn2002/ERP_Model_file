@extends('layouts.admin')

@section('title', 'Create Purchase Order')
@section('page-title', 'Create Purchase Order')
@section('icon', 'plus-circle')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-plus-circle me-2"></i> Create Purchase Order
    </div>
    <div class="card-body">
        <form action="{{ route('admin.purchase-orders.store') }}" method="POST" id="po-form">
            @csrf
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select class="form-select @error('supplier_id') is-invalid @enderror" 
                                id="supplier_id" name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $id => $name)
                                <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="order_date" class="form-label">Order Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('order_date') is-invalid @enderror" 
                               id="order_date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required>
                        @error('order_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="expected_date" class="form-label">Expected Date</label>
                <input type="date" class="form-control @error('expected_date') is-invalid @enderror" 
                       id="expected_date" name="expected_date" value="{{ old('expected_date') }}">
                @error('expected_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <h5 class="mt-4">Order Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered" id="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Line Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr>
                            <td>
                                <select class="form-select product-select" name="items[0][product_id]" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->cost_price }}">
                                            {{ $product->name }} ({{ $product->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" class="form-control quantity" name="items[0][quantity]" min="1" value="1" required>
                            </td>
                            <td>
                                <input type="number" class="form-control unit-price" name="items[0][unit_price]" step="0.01" min="0" value="0" required>
                            </td>
                            <td>
                                <span class="line-total">0.00</span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-item">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                            <td colspan="2"><span id="subtotal">0.00</span></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Tax (5%):</strong></td>
                            <td colspan="2"><span id="tax">0.00</span></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                            <td colspan="2"><span id="grand-total">0.00</span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <button type="button" class="btn btn-secondary mt-2" id="add-item">
                <i class="fas fa-plus"></i> Add Item
            </button>
            
            <div class="mb-3 mt-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Purchase Order
                </button>
                <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let itemIndex = 1;

$(document).on('change', '.product-select', function() {
    const price = $(this).find(':selected').data('price') || 0;
    const row = $(this).closest('tr');
    row.find('.unit-price').val(price);
    calculateRow(row);
});

$(document).on('input', '.quantity, .unit-price', function() {
    const row = $(this).closest('tr');
    calculateRow(row);
});

function calculateRow(row) {
    const quantity = parseFloat(row.find('.quantity').val()) || 0;
    const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
    const lineTotal = quantity * unitPrice;
    row.find('.line-total').text(lineTotal.toFixed(2));
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    $('.line-total').each(function() {
        subtotal += parseFloat($(this).text()) || 0;
    });
    
    const tax = subtotal * 0.05;
    const grandTotal = subtotal + tax;
    
    $('#subtotal').text(subtotal.toFixed(2));
    $('#tax').text(tax.toFixed(2));
    $('#grand-total').text(grandTotal.toFixed(2));
}

$('#add-item').click(function() {
    const newRow = `
        <tr>
            <td>
                <select class="form-select product-select" name="items[${itemIndex}][product_id]" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->cost_price }}">
                            {{ $product->name }} ({{ $product->sku }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="form-control quantity" name="items[${itemIndex}][quantity]" min="1" value="1" required>
            </td>
            <td>
                <input type="number" class="form-control unit-price" name="items[${itemIndex}][unit_price]" step="0.01" min="0" value="0" required>
            </td>
            <td>
                <span class="line-total">0.00</span>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-item">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>
    `;
    $('#items-body').append(newRow);
    itemIndex++;
});

$(document).on('click', '.remove-item', function() {
    if ($('#items-body tr').length > 1) {
        $(this).closest('tr').remove();
        calculateTotals();
    }
});
</script>
@endpush