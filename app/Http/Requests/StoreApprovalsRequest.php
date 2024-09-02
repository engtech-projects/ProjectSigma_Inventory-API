<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApprovalsRequest extends FormRequest
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
            'form' => [
                "required",
                "string",
                Rule::unique("approvals", "form")
            ],
            'approvals' => [
                "nullable",
                "array",
            ],
            'approvals.*.type' => [
                "required",
                "string",
            ],
            'approvals.*.userselector' => [
                "nullable",
                "boolean",
            ],
            'approvals.*.user_id' => [
                "nullable",
                "integer",
                // "exists:user,id",
                Rule::notIn([1]),
            ],
        ];
    }
}
