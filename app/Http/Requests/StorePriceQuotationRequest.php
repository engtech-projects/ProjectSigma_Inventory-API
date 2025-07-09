<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePriceQuotationRequest extends FormRequest
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
            'supplier_id' => ['required', 'exists:request_supplier,id'],
            'date' => ['required', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_no' => ['nullable', 'string', 'max:255'],
            'conso_reference_no' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array'],
            'items.*.item_id' => [
                'required',
                Rule::exists('item_profile', 'id')->where('is_approved', 1)
            ],
            'items.*.item_description' => 'nullable|string',
            'items.*.specification' => 'nullable|string',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.uom' => 'nullable|string',
            'items.*.preferred_brand' => 'nullable|string',
            'items.*.actual_brand' => ['nullable', 'string'],
            'items.*.unit_price' => ['nullable', 'numeric'],
            'items.*.remarks_during_canvass' => ['nullable', 'string'],
        ];

    }
}
