<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestTurnoverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approved_by' => ['nullable', 'string', 'max:255'],
            'received_date' => ['nullable', 'date'],
            'received_name' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'received_date.date' => 'Please provide a valid date.',
            'received_name.max' => 'Received name cannot exceed 255 characters.',
        ];
    }
}
