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
    protected function prepareForValidation()
    {
        if (gettype($this->details) == "string") {
            $this->merge([
                "details" => json_decode($this->details, true)
            ]);
        }
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
                'nullable',
                'string',
                new Enum(AssignTypes::class),
            ],
            'effectivity' => 'required|string',
            'details' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return $fail('The ' . $attribute . ' must not be empty.');
                    }

                    foreach ($value as $item) {
                        if (empty($item['item_id']) || empty($item['uom_id']) || empty($item['unit_price']) || empty($item['quantity'])) {
                            return $fail('The ' . $attribute . ' must contain the required fields.');
                        }
                    }
                },
            ],
            'details.*.item_id' => 'required|exists:item_profile,id',
            'details.*.uom_id' => 'required|exists:setup_uom,id',
            'details.*.unit_price' => 'required|numeric',
            'details.*.quantity' => 'required|numeric',
            ...$this->storeApprovals(),
        ];
    }
}
