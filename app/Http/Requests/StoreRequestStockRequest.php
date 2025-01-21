<?php

namespace App\Http\Requests;

use App\Http\Traits\HasApprovalValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequestStockRequest extends FormRequest
{
    use HasApprovalValidation;
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
            'request_for' => 'required|string|max:255',
            'warehouse_id' => 'required|numeric|exists:warehouse,id',
            'office_project' => 'required|numeric|exists:projects,id',
            'office_project_address' => 'required|string|max:255',
            'date_prepared' => 'required|date',
            'date_needed' => 'required|date',
            'equipment_no' => 'required|string|max:255|unique:request_stocks,equipment_no',
            'items' => 'required|array',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'required|integer|exists:setup_uom,id',
            'items.*.item_id' => 'required|exists:item_profile,id',
            'items.*.specification' => 'nullable|string',
            'items.*.preferred_brand' => 'nullable|string',
            'items.*.reason' => 'nullable|string',
            'items.*.location' => 'nullable|string',
            'items.*.location_qty' => 'nullable|numeric|min:1',
            'items.*.is_approved' => 'boolean',
            'items.*.type_of_request' => 'nullable|string',
            'items.*.contact_no' => 'nullable|string',
            'items.*.remarks' => 'nullable|string',
            'items.*.current_smr' => 'nullable|string',
            'items.*.previous_smr' => 'nullable|string',
            'items.*.unused_smr' => 'nullable|string',
            'items.*.next_smr' => 'nullable|string',
            ...$this->storeApprovals(),
        ];
    }

    public function messages()
    {
        return [
            'date_needed.required' => 'The date when the stock is needed is required.',
            'items.required' => 'At least one item must be specified.',
            'items.*.item_id.exists' => 'The selected item does not exist.',
            'items.*.quantity.min' => 'The quantity must be at least 1.',
        ];
    }
}
