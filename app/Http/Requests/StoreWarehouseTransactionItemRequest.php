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
            'item_id' => 'required|exists:item_profile,id',
            'parent_id' => 'nullable|exists:warehouse_transaction_items,id',
            'quantity' => 'required|numeric|min:0',
            'uom' => 'required|numeric|exists:setup_uom,id',
            'metadata' => 'nullable|array',
        ];
    }
}
