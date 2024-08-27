<?php

namespace App\Http\Requests;

use App\Enums\InventoryType;
use App\Enums\ActiveStatus;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\HasApprovalValidation;
use Illuminate\Validation\Rules\Enum;

class StoreItemProfileRequest extends FormRequest
{
    // use HasApprovalValidation;
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
            'item_profiles.*.sku' => [
                "required",
                "string",
                "max:6",
                "unique:item_profile,sku"
            ],
            'item_profiles.*.item_description' => [
                "required",
                "string",
                "max:255"
            ],
            'item_profiles.*.thickness_val' => [
                "numeric",
                "nullable"
            ],
            'item_profiles.*.thickness_uom' => [
                "numeric",
                "nullable",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.length_val' => [
                "numeric",
                "nullable"
            ],
            'item_profiles.*.length_uom' => [
                "numeric",
                "nullable",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.width_val' => [
                "numeric",
                "nullable"
            ],
            'item_profiles.*.width_uom' => [
                "numeric",
                "nullable",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.height_val' => [
                "numeric",
                "nullable"
            ],
            'item_profiles.*.height_uom' => [
                "numeric",
                "nullable",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.outside_diameter_val' => [
                "numeric",
                "nullable"
            ],
            'item_profiles.*.outside_diameter_uom' => [
                "numeric",
                "nullable",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.inside_diameter_val' => [
                "numeric",
                "nullable"
            ],
            'item_profiles.*.inside_diameter_uom' => [
                "numeric",
                "nullable",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.specification' => [
                "required",
                "string",
                "max:255"
            ],
            'item_profiles.*.volume' => [
                "numeric",
                "nullable"
            ],
            'item_profiles.*.grade' => [
                "string",
                "nullable",
                "max:255"
            ],
            'item_profiles.*.color' => [
                "string",
                "nullable",
                "max:255"
            ],
            'item_profiles.*.uom' => [
                "required",
                "numeric",
                "exists:setup_uom,id"
            ],
            'item_profiles.*.uom_conversion_group_id' => [
                "numeric",
                "exists:setup_uom_group,id"
            ],
            'item_profiles.*.uom_conversion_value' => [
                "numeric",
                "nullable"
            ],
            'item_profiles.*.inventory_type' => [
                "required",
                "string",
                new Enum(InventoryType::class)
            ],
            'item_profiles.*.active_status' => [
                "required",
                "string",
                new Enum(ActiveStatus::class)
            ],
            'item_profiles.*.is_approved' => [
                "boolean"
            ],
        ];
    }
}
