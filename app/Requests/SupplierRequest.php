<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplier = $this->route('supplier');
        
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers')->ignore($supplier),
            ],
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'The supplier code is required.',
            'code.unique' => 'This code is already taken.',
            'name.required' => 'The supplier name is required.',
            'email.required' => 'The email is required.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => 'The phone number is required.',
        ];
    }
}