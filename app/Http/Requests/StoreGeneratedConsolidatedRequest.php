<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGeneratedConsolidatedRequest extends FormRequest
{
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
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purpose' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'rs_ids' => 'required|array|min:1',
            'rs_ids.*' => 'required|integer|exists:request_requisition_slips,id',
            'items' => 'required|array|min:1',
            'items.*.item_description' => 'required|string',
            'items.*.specification' => 'nullable|string',
            'items.*.preferred_brand' => 'nullable|string',
            'items.*.unit' => 'required|string',
            'items.*.rs_item_ids' => 'required|array|min:1',
            'items.*.rs_item_ids.*.rs_id' => 'required|integer',
            'items.*.rs_item_ids.*.rs_item_id' => 'required|integer',
            'items.*.rs_item_ids.*.quantity' => 'required|numeric|min:0',
        ];
    }
    public function messages(): array
    {
        return [
            'purpose.required' => 'The purpose of the consolidation is required.',
            'rs_ids.required' => 'Please select at least one requisition slip.',
            'rs_ids.*.exists' => 'One or more selected requisition slips do not exist.',
        ];
    }
}
