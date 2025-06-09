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
            'specification' => 'nullable|string|max:255',
            'actual_brand_purchase' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:1',
            'quantity' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:255',
            'metadata' => 'required|array',
            'metadata.unit_price'         => 'required|numeric|min:0',
            'metadata.actual_brand_purchase' => 'required|string|max:255',
            'metadata.specification'      => 'nullable|string|max:255',
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
            'metadata.unit_price.required' => 'The unit price is required.',
            'metadata.actual_brand_purchase.required' => 'The actual brand purchase is required.',
        ];

    }
}
