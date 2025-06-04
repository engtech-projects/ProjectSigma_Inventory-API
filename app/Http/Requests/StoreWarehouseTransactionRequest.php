<?php

namespace App\Http\Requests;

use App\Http\Traits\HasApprovalValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseTransactionRequest extends FormRequest
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
            'warehouse_id' => 'required|exists:warehouse,id',
            'transaction_type' => 'required|in:Receiving,Transfer,Withdraw,Return',
            'charging_type' => 'nullable|string',
            'charging_id' => 'nullable|integer',
            'items' => 'required|array',
            'items.*' => 'required|array',
            'items.*.item_id' => 'required|exists:item_profile,id',
            'items.*.parent_id' => 'nullable|exists:warehouse_transaction_items,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.uom' => 'required|numeric|exists:setup_uom,id',
            'metadata' => 'nullable|array',
            'metadata.rs_id' => 'nullable|integer|exists:request_stocks,id',
            'metadata.rs_reference_no' => 'nullable|string',
            'metadata.equipment_no' => 'nullable|string',
            'metadata.transaction_date' => 'nullable|date',
            'metadata.project_code' => 'nullable|string',
            'metadata.supplier_id' => 'nullable|integer|exists:suppliers,id',
            'metadata.terms_of_payment' => 'nullable|string',
            'metadata.particulars' => 'nullable|string',
            'metadata.po_id' => 'nullable|integer|exists:purchase_orders,id',
            'metadata.is_petty_cash' => 'nullable|boolean',
            ...$this->storeApprovals(),
        ];
    }
}
