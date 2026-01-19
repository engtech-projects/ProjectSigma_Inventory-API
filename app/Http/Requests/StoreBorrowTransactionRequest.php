<?php

namespace App\Http\Requests;

use App\Http\Traits\HasApprovalValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreBorrowTransactionRequest extends FormRequest
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
        if (gettype($this->items) == "string") {
            $this->merge([
                "items" => json_decode($this->items, true)
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'reference_no' => ['nullable', 'string', 'max:255', 'unique:borrow_transactions,reference_no'],
            'warehouse_id' => ['required', 'exists:setup_warehouses,id'],
            'date_time_borrowed' => ['required', 'date'],
            'borrowed_by' => ['required', 'string', 'max:255'],
            'borrowed_contact_no' => ['required', 'string', 'max:255'],
            'returned_by' => ['nullable', 'string', 'max:255'],
            'date_time_returned' => ['nullable', 'date', 'after:date_time_borrowed'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],

            // Items validation
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:item_profile,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            ...$this->storeApprovals(),
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Warehouse is required.',
            'warehouse_id.exists' => 'The selected warehouse does not exist.',
            'items.required' => 'At least one item must be added to the borrow transaction.',
            'items.*.item_id.required' => 'Each item must have a valid item selected.',
            'items.*.item_id.exists' => 'The selected item does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'date_time_returned.after' => 'Return date must be after the borrowed date.',
        ];
    }
}
