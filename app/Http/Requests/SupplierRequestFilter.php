<?php

namespace App\Http\Requests;

use App\Enums\OwnershipType;
use App\Enums\RequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SupplierRequestFilter extends FormRequest
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
            'company_name' => [
                "nullable",
                "string"
            ],
            'type_of_ownership' => [
                "nullable",
                "string",
                new Enum(OwnershipType::class)
            ],
            'contact_person_name' => [
                "nullable",
                "string"
            ],
            'supplier_code' => [
                "nullable",
                "string"
            ],
            'request_status' => [
                "nullable",
                "string",
                new Enum(RequestStatus::class)
            ],
        ];
    }
}
