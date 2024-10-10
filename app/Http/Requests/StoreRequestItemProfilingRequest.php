<?php

namespace App\Http\Requests;

use App\Enums\InventoryType;
use App\Enums\ItemProfileActiveStatus;
use App\Http\Traits\HasApprovalValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRequestItemProfilingRequest extends FormRequest
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
        if (gettype($this->item_profiles) == "string") {
            $this->merge([
                "item_profiles" => json_decode($this->item_profiles, true)
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
            'item_profiles' => 'required|array',
            'item_profiles.*' => 'required|array',
            'item_profiles.*.item_code' => [
                "required",
                "string",
                "max:255",
                "unique:item_profile,item_code"
            ],
            'item_profiles.*.item_description' => [
                "required",
                "string",
                "max:255"
            ],
            'item_profiles.*.thickness_val' => [
                "numeric",
                "nullable",
                "required_without_all:item_profiles.*.length_val,item_profiles.*.width_val,item_profiles.*.height_val,item_profiles.*.outside_diameter_val,item_profiles.*.inside_diameter_val,item_profiles.*.volume_val,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification"
            ],
            'item_profiles.*.thickness_uom' => [
                "numeric",
                "nullable",
                "required_with:item_profiles.*.thickness_val",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.length_val' => [
                "numeric",
                "nullable",
                "required_without_all:item_profiles.*.thickness_val,item_profiles.*.width_val,item_profiles.*.height_val,item_profiles.*.outside_diameter_val,item_profiles.*.inside_diameter_val,item_profiles.*.volume_val,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification"
            ],
            'item_profiles.*.length_uom' => [
                "numeric",
                "nullable",
                "required_with:item_profiles.*.length_val",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.width_val' => [
                "numeric",
                "nullable",
                "required_without_all:item_profiles.*.length_val,item_profiles.*.thickness_val,item_profiles.*.height_val,item_profiles.*.outside_diameter_val,item_profiles.*.inside_diameter_val,item_profiles.*.volume_val,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification"
            ],
            'item_profiles.*.width_uom' => [
                "numeric",
                "nullable",
                "required_with:item_profiles.*.width_val",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.height_val' => [
                "numeric",
                "nullable",
                "required_without_all:item_profiles.*.length_val,item_profiles.*.thickness_val,item_profiles.*.width_val,item_profiles.*.outside_diameter_val,item_profiles.*.inside_diameter_val,item_profiles.*.volume_val,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification"
            ],
            'item_profiles.*.height_uom' => [
                "numeric",
                "required_with:item_profiles.*.height_val",
                "nullable",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.outside_diameter_val' => [
                "numeric",
                "nullable",
                "required_without_all:item_profiles.*.length_val,item_profiles.*.thickness_val,item_profiles.*.height_val,item_profiles.*.width_val,item_profiles.*.inside_diameter_val,item_profiles.*.volume_val,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification"
            ],
            'item_profiles.*.outside_diameter_uom' => [
                "numeric",
                "nullable",
                "required_with:item_profiles.*.outside_diameter_val",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.inside_diameter_val' => [
                "numeric",
                "nullable",
                "required_without_all:item_profiles.*.length_val,item_profiles.*.thickness_val,item_profiles.*.height_val,item_profiles.*.width_val,item_profiles.*.outside_diameter_val,item_profiles.*.volume_val,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification"
            ],
            'item_profiles.*.inside_diameter_uom' => [
                "numeric",
                "nullable",
                "required_with:item_profiles.*.inside_diameter_val",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.specification' => [
                "string",
                "max:255",
                "required_without_all:item_profiles.*.length_val,item_profiles.*.thickness_val,item_profiles.*.height_val,item_profiles.*.width_val,item_profiles.*.inside_diameter_val,item_profiles.*.volume_val,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.outside_diameter_val"
            ],
            'item_profiles.*.volume_val' => [
                "numeric",
                "nullable",
                "required_without_all:item_profiles.*.length_val,item_profiles.*.thickness_val,item_profiles.*.height_val,item_profiles.*.width_val,item_profiles.*.inside_diameter_val,item_profiles.*.outside_diameter_val,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification"
            ],
            'item_profiles.*.volume_uom' => [
                "numeric",
                "nullable",
                "required_with:item_profiles.*.volume_val",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.grade' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length_val,item_profiles.*.thickness_val,item_profiles.*.height_val,item_profiles.*.width_val,item_profiles.*.inside_diameter_val,item_profiles.*.volume_val,item_profiles.*.color,item_profiles.*.outside_diameter_val,item_profiles.*.specification"

            ],
            'item_profiles.*.color' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length_val,item_profiles.*.thickness_val,item_profiles.*.height_val,item_profiles.*.width_val,item_profiles.*.inside_diameter_val,item_profiles.*.volume_val,item_profiles.*.outside_diamter_val,item_profiles.*.grade,item_profiles.*.specification"
            ],
            'item_profiles.*.item_group' => [
                "string",
                "required",
                "max:255"
            ],
            'item_profiles.*.sub_item_group' => [
                "string",
                "required",
                "max:255"
            ],
            'item_profiles.*.uom' => [
                "required",
                "numeric",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.uom_conversion_group_id' => [
                "numeric",
                "nullable",
                "exists:setup_uom_group,id"
            ],
            'item_profiles.*.uom_conversion_value' => [
                "numeric",
                "nullable"
            ],
            'item_profiles.*.inventory_type' => [
                "required",
                "string",
                "max:255",
                new Enum(InventoryType::class)
            ],
            'item_profiles.*.active_status' => [
                "required",
                "string",
                "max:255",
                new Enum(ItemProfileActiveStatus::class)
            ],
            'item_profiles.*.is_approved' => [
                "boolean"
            ],
            ...$this->storeApprovals(),
        ];
    }

}
