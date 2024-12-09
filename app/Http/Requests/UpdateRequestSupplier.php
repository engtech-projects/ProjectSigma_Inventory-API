<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'supplier_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique("request_supplier", "supplier_code")->ignore($this->route("resource"), 'id')->whereNull('deleted_at'),
            ],
            'company_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique("request_supplier", "company_name")->ignore($this->route("resource"), 'id')->whereNull('deleted_at'),
            ],
            'company_address' => [
                'nullable',
                'string',
                'max:255'
            ],
            'company_contact_number' => [
                'nullable',
                'string',
                'max:20'
            ],
            'company_email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique("request_supplier", "company_email")->ignore($this->route("resource"), 'id')->whereNull('deleted_at'),
            ],
            'contact_person_name' => [
                'nullable',
                'string',
                'max:255'
            ],
            'contact_person_number' => [
                'nullable',
                'string',
                'max:20'
            ],
            'contact_person_designation' => [
                'nullable',
                'string',
                'max:255'
            ],
            'type_of_ownership' => [
                'nullable',
                'string',
                'in:Single Proprietorship,Partnership,Corporation'
            ],
            'nature_of_business' => [
                'nullable',
                'string',
                'max:255'
            ],
            'products_services' => [
                'nullable',
                'string',
                'max:255'
            ],
            'classification' => [
                'nullable',
                'string',
                'max:255'
            ],
            'tin' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique("request_supplier", "tin")->ignore(request()->route("resource"), 'id')->whereNull('deleted_at'),
            ],
            'terms_and_conditions' => [
                'nullable',
                'string'
            ],
            'filled_by' => [
                'nullable',
                'string',
                'max:255'
            ],
            'filled_designation' => [
                'nullable',
                'string',
                'max:255'
            ],
            'filled_date' => [
                'nullable',
                'date_format:Y-m-d'
            ],
            'requirements_complete' => [
                'nullable',
                'string',
                'in:Yes,No'
            ],
            'remarks' => [
                'nullable',
                'string'
            ],
        ];
    }
}
