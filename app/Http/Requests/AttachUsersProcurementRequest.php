<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachUsersProcurementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Check if items data is in string format and convert to array
        if (gettype($this->user_ids) === "string") {
            $this->merge([
                "user_ids" => json_decode($this->user_ids, true),
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
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ];
    }
}
