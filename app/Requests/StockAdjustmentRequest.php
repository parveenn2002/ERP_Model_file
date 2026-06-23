<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'required|string|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select a product.',
            'product_id.exists' => 'The selected product is invalid.',
            'warehouse_id.required' => 'Please select a warehouse.',
            'warehouse_id.exists' => 'The selected warehouse is invalid.',
            'type.required' => 'Please select an adjustment type.',
            'type.in' => 'Invalid adjustment type.',
            'quantity.required' => 'The quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'notes.required' => 'Please provide a reason for the stock adjustment.',
            'notes.min' => 'Notes must be at least 3 characters.',
        ];
    }
}