<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestItemProfilingRequest extends FormRequest
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
            'approvals' => [
                "required",
                "json"
            ],
            'created_by' => [
                "required",
                "string",
                "exists:user,id"
            ],
            'item_profile_ids' => [
                "required",
                "array"
            ],
            'item_profile_ids.*' => [
                "required",
                "integer",
                "exists:item_profile,id"
            ],
        ];
    }

}
