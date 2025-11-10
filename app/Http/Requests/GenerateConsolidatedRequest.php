<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateConsolidatedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        if (is_string($this->slip_ids)) {
            $this->merge([
                'rs_ids' => json_decode($this->slip_ids, true),
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
            'rs_ids' => ['required', 'array', 'min:1'],
            'rs_ids.*' => ['required', 'integer', 'distinct', 'exists:request_requisition_slips,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'rs_ids.required' => 'Please select at least one requisition slip.',
            'rs_ids.array' => 'Slip IDs must be provided as an array.',
            'rs_ids.*.exists' => 'One or more selected slips do not exist.',
        ];
    }
}
