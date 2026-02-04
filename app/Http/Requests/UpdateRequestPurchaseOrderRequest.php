<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestPurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name_on_receipt' => isset($this->name_on_receipt) ? trim((string) $this->name_on_receipt) : $this->name_on_receipt,
            'delivered_to' => isset($this->delivered_to) ? trim((string) $this->delivered_to) : $this->delivered_to,
        ]);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name_on_receipt' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            'delivered_to' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
