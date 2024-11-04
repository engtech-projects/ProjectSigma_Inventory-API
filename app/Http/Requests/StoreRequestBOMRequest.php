<?php

namespace App\Http\Requests;

use App\Enums\AssignTypes;
use App\Http\Traits\HasApprovalValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRequestBOMRequest extends FormRequest
{
    use HasApprovalValidation;
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
            'assignment_id' => 'nullable|integer',
            'assignment_type' => [
                "nullable",
                "string",
                new Enum(AssignTypes::class)
            ],
            'effectivity' => 'required|string',
            'details' => 'required|array',
            'details.*' => 'required|array',
            'details.request_bom_id' => 'required|numeric|exists:request_bom,id',
            'details.item_id' => 'required|exists:item_profile,id',
            'details.uom_id' => 'required|exists:setup_uom,id',
            'details.unit_price' => 'required|numeric',
            'details.quantity' => 'required|numeric',
            ...$this->storeApprovals(),
        ];
    }
}
