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
            'item_profiles.*.thickness' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.width,item_profiles.*.height,item_profiles.*.outside_diameter,item_profiles.*.inside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.length' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.thickness,item_profiles.*.width,item_profiles.*.height,item_profiles.*.outside_diameter,item_profiles.*.inside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.width' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.outside_diameter,item_profiles.*.inside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.height' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.width,item_profiles.*.outside_diameter,item_profiles.*.inside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.outside_diameter' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.inside_diameter' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.outside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.angle' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.outside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.size' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.outside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.volume' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.outside_diameter,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.weight' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.outside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.grade' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.outside_diameter,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.volts' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.outside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.plates' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.outside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.part_number"
            ],
            'item_profiles.*.part_number' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.outside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates"
            ],
            'item_profiles.*.specification' => [
                "string",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.volume,item_profiles.*.color,item_profiles.*.grade,item_profiles.*.outside_diameter,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
            ],
            'item_profiles.*.color' => [
                "string",
                "nullable",
                "max:255",
                "required_without_all:item_profiles.*.length,item_profiles.*.thickness,item_profiles.*.height,item_profiles.*.width,item_profiles.*.inside_diameter,item_profiles.*.volume,item_profiles.*.outside_diameter,item_profiles.*.grade,item_profiles.*.specification,item_profiles.*.angle,item_profiles.*.size,item_profiles.*.weight,item_profiles.*.volts,item_profiles.*.plates,item_profiles.*.part_number"
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
                "max:255",
                "exists:setup_uom,id"
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
