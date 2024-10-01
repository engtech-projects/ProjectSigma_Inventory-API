<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkItemProfileRequest extends FormRequest
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
        // Check if items data is in string format and convert to array
        if (gettype($this->items) === "string") {
            $this->merge([
                "processed" => json_decode($this->items, true),
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
            "processed" => ['required', 'array'],
            "processed.*" => 'required|array',

            // Required fields
            "processed.*.sku" => 'required|string|max:10',
            "processed.*.item_description.value" => 'required|string|max:255',
            "processed.*.uom.uom_group_id" => 'required|exists:setup_uom_group,id',
            "processed.*.item_group.value" => 'required|string|max:50',
            "processed.*.sub_item_group.value" => 'required|string|max:50',
            "processed.*.inventory_type.value" => 'required|string|max:50',

            "processed.*.thickness_val.value" => 'nullable|numeric',
            "processed.*.thickness_uom.uom_id" => 'nullable|exists:setup_uom,id',
            "processed.*.length_val.value" => 'nullable|numeric',
            "processed.*.length_uom.uom_id" => 'nullable|exists:setup_uom,id',
            "processed.*.width_val.value" => 'nullable|numeric',
            "processed.*.width_uom.uom_id" => 'nullable|exists:setup_uom,id',
            "processed.*.height_val.value" => 'nullable|numeric',
            "processed.*.height_uom.uom_id" => 'nullable|exists:setup_uom,id',
            "processed.*.outside_diameter_val.value" => 'nullable|numeric',
            "processed.*.outside_diameter_uom.uom_id" => 'nullable|exists:setup_uom,id',
            "processed.*.inside_diameter_val.value" => 'nullable|numeric',
            "processed.*.inside_diameter_uom.uom_id" => 'nullable|exists:setup_uom,id',
            "processed.*.volume_val.value" => 'nullable|numeric',
            "processed.*.volume_uom.uom_id" => 'nullable|exists:setup_uom,id',
            "processed.*.specification.value" => 'nullable|string|max:255',
            "processed.*.grade.value" => 'nullable|string|max:50',
            "processed.*.color.value" => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'processed.required' => 'The processed items are required.',
            'processed.*.sku.required' => 'The SKU is required.',
            'processed.*.item_description.value.required' => 'Item description is required.',
            'processed.*.uom.uom_group_id.required' => 'UOM group ID is required.',
            'processed.*.uom.uom_group_id.exists' => 'The selected UOM group ID is invalid.',
            'processed.*.item_group.value.required' => 'Item group is required.',
            'processed.*.sub_item_group.value.required' => 'Sub item group is required.',
            'processed.*.inventory_type.value.required' => 'Inventory type is required.',
            'processed.*.thickness_val.value.numeric' => 'Thickness value must be numeric.',
            // Add other custom messages as needed
        ];
    }
}
