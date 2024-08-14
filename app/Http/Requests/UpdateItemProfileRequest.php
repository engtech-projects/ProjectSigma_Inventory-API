<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemProfileRequest extends FormRequest
{
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
            'sku' => 'string|max:255',
            'item_description' => 'required|string|max:255',
            'thickness_val' => 'numeric',
            'thickness_uom' => 'numeric',
            'length_val' => 'numeric',
            'length_uom' => 'numeric',
            'width_val' => 'numeric',
            'width_uom' => 'numeric',
            'height_val' => 'numeric',
            'height_uom' => 'numeric',
            'outside_diameter_val' => 'numeric',
            'outside_diameter_uom' => 'numeric',
            'inside_diameter_val' => 'numeric',
            'inside_diameter_uom' => 'numeric',
            'specification' => 'required|string|max:255',
            'grade' => 'string|max:255',
            'color' => 'required|string|max:255',
            'uom' => 'required|string',
            'inventory_type' => 'string',
            'active_status' => 'string',
            'is_approved' => 'boolean'
        ];
    }
}
