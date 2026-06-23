<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $warehouse = $this->route('warehouse');
        
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouses')->ignore($warehouse),
            ],
            'name' => 'required|string|max:255',
            'location' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'The warehouse code is required.',
            'code.unique' => 'This code is already taken.',
            'name.required' => 'The warehouse name is required.',
        ];
    }
}