<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApprovalsRequest extends FormRequest
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
                "nullable",
                "string",
            ],
            'approvals' => [
                "nullable",
                "array",
            ],
            'approvals.*.type' => [
                "nullable",
                "string",
            ],
            'approvals.*.userselector' => [
                "nullable",
                "boolean",
            ],
            'approvals.*.user_id' => [
                "nullable",
                // "exists:user,id",
                "required_if:user.type,Employee,"
            ],
        ];
    }
}
