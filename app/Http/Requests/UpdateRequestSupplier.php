<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestSupplier extends FormRequest
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
            'supplier_code' => 'sometimes|string|max:255|unique:request_supplier,supplier_code',
            'company_name' => 'sometimes|string|max:255|unique:request_supplier,company_name',
            'company_address' => 'sometimes|string|max:255',
            'company_contact_number' => 'sometimes|numeric|max:20',
            'company_email' => 'sometimes|string|email|max:255|unique:request_supplier,company_email',
            'contact_person_name' => 'sometimes|string|max:255',
            'contact_person_number' => 'sometimes|string|max:20',
            'contact_person_designation' => 'sometimes|string|max:255',
            'type_of_ownership' => 'sometimes|string|in:Single Proprietorship,Partnership,Corporation',
            'nature_of_business' => 'sometimes|string|max:255',
            'products_services' => 'sometimes|string|max:255',
            'classification' => 'sometimes|string|max:255',
            'tin' => 'sometimes|string|max:15|unique:request_supplier,tin',
            'terms_and_conditions' => 'sometimes|string',
            'filled_by' => 'sometimes|string|max:255',
            'filled_designation' => 'sometimes|string|max:255',
            'filled_date' => 'sometimes|date_format:Y-m-d',
            'requirements_complete' => 'sometimes|string|in:Yes,No',
            'remarks' => 'nullable|string',
        ];
    }
}
