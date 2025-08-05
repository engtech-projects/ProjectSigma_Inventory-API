<?php

namespace App\Http\Requests;

use App\Http\Traits\HasApprovalValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseTransactionRequest extends FormRequest
{
    use HasApprovalValidation;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (is_string($this->items)) {
            $this->merge([
                "items" => json_decode($this->items, true)
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => 'required|exists:setup_warehouses,id',
            'transaction_type' => 'required|in:Receiving,Transfer,Withdraw,Return',
            'charging_type' => 'nullable|string',
            'charging_id' => 'nullable|integer',
            'items' => 'required|array',
            'items.*' => 'required|array',
            'items.*.item_id' => 'required|exists:item_profile,id',
            'items.*.parent_id' => 'nullable|exists:warehouse_transaction_items,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.uom' => 'required|numeric|exists:setup_uom,id',
            ...$this->storeApprovals(),
        ];
    }
}
