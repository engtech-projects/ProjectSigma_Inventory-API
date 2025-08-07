<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionMaterialReceivingItemRequest extends FormRequest
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
            "specification" => ["nullable", "string", "max:255"],
            "actual_brand_purchase" => ["nullable", "string", "max:255"],
            "unit_price" => ["nullable", "numeric", "min:1"],
        ];
    }
}
