<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestTurnoverRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'from_warehouse_id' => ['required', 'exists:setup_warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:setup_warehouses,id', 'different:from_warehouse_id'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:item_profile,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.uom' => ['required', 'exists:setup_uom,id'],
            'items.*.condition' => ['nullable', 'string', 'max:100'],
            'items.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_warehouse_id.different' => 'The destination warehouse must be different from the source warehouse.',
            'items.required' => 'At least one item is required.',
            'items.*.item_id.required' => 'Item is required.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.uom.required' => 'Unit of measure is required.',
        ];
    }
}
