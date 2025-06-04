<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseTransactionItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => 'nullable|exists:item_profile,id',
            'parent_id' => 'nullable|exists:warehouse_transaction_items,id',
            'quantity' => 'nullable|numeric|min:0',
            'uom' => 'nullable|numeric|exists:setup_uom,id',
            'remarks' => 'required|string|max:255',
            'metadata' => 'nullable|array',
            'metadata.unit_price' => 'required|numeric|min:0',
            'metadata.actual_brand_purchase' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'metadata.unit_price.required' => 'The unit price is required and cannot be null.',
            'metadata.actual_brand_purchase.required' => 'The actual brand purchase is required and cannot be null.',
        ];

    }
}
