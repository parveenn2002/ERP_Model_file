<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('products');
        
        return [
            'category_id' => 'required|exists:categories,id',
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products')->ignore($product),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'sku.required' => 'The SKU field is required.',
            'sku.unique' => 'This SKU is already taken.',
            'name.required' => 'The product name is required.',
            'unit.required' => 'Please select a unit.',
            'cost_price.required' => 'The cost price is required.',
            'cost_price.min' => 'The cost price must be greater than 0.',
            'reorder_level.required' => 'The reorder level is required.',
            'reorder_level.min' => 'The reorder level must be at least 0.',
        ];
    }
}