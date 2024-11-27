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
            "processed.*.item_code" => 'required|string|max:10',
            "processed.*.item_description.value" => 'required|string|max:255',
            "processed.*.uom.uom_id" => 'required|exists:setup_uom,id',
            "processed.*.item_group.value" => 'required|string|max:50',
            "processed.*.sub_item_group.value" => 'required|string|max:50',
            "processed.*.inventory_type.value" => 'required|string|max:50',
            "processed.*.angle.value" => 'nullable|string|max:50',
            "processed.*.size.value" => 'nullable|string|max:50',
            "processed.*.volume.value" => 'nullable|string|max:50',
            "processed.*.weight.value" => 'nullable|string|max:50',
            "processed.*.volts.value" => 'nullable|string|max:50',
            "processed.*.plates.value" => 'nullable|string|max:50',
            "processed.*.part_number.value" => 'nullable|string|max:50',
            "processed.*.thickness.value" => 'nullable|string|max:50',
            "processed.*.length.value" => 'nullable|string|max:50',
            "processed.*.width.value" => 'nullable|string|max:50',
            "processed.*.height.value" => 'nullable|string|max:50',
            "processed.*.outside_diameter.value" => 'nullable|string|max:50',
            "processed.*.inside_diameter.value" => 'nullable|string|max:50',
            "processed.*.specification.value" => 'nullable|string|max:255',
            "processed.*.grade.value" => 'nullable|string|max:50',
            "processed.*.color.value" => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'processed.required' => 'The processed items are required.',
            'processed.*.item_code.required' => 'The Item code is required.',
            'processed.*.item_description.value.required' => 'Item description is required.',
            'processed.*.uom.uom_id.required' => 'UOM is required.',
            'processed.*.item_group.value.required' => 'Item group is required.',
            'processed.*.sub_item_group.value.required' => 'Sub item group is required.',
            'processed.*.inventory_type.value.required' => 'Inventory type is required.',
            'processed.*.thickness.value.numeric' => 'Thickness value must be numeric.',
            // Add other custom messages as needed
        ];
    }
}
