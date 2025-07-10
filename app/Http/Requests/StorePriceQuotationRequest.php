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
            'items' => ['required', 'array'],
            'items.*.item_id' => [
                'required',
                Rule::exists('item_profile', 'id')->where('is_approved', 1)
            ],
            'items.*.actual_brand' => ['nullable', 'string'],
            'items.*.unit_price' => ['nullable', 'numeric'],
            'items.*.remarks_during_canvass' => ['nullable', 'string'],
        ];

    }
}
