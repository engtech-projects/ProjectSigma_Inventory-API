<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriceQuotationItemRequest extends FormRequest
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
            'actual_brand' => ['nullable', 'string', 'max:255'],
            'unit_price' => ['nullable', 'numeric', 'min:1'],
            'remarks_during_canvass' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
