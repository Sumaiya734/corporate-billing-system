<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // change to true or implement admin check
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'email' => 'nullable|email|max:191|unique:customers,email',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'connection_address' => 'nullable|string',
            'id_type' => 'nullable|string|max:60',
            'id_number' => 'nullable|string|max:100',
            'product_id' => 'nullable|integer|exists:products,id',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6|confirmed' // if you create linked user
        ];
    }
}