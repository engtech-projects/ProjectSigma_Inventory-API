<?php

namespace App\Http\Requests;

use App\Http\Traits\HasApprovalValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequestSupplier extends FormRequest
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
            'supplier_code' => 'required|string|max:255|unique:request_supplier,supplier_code',
            'company_name' => 'required|string|max:255|unique:request_supplier,company_name',
            'company_address' => 'required|string|max:255',
            'company_contact_number' => 'required|string|max:20',
            'company_email' => 'required|string|email|max:255|unique:request_supplier,company_email',
            'contact_person_name' => 'required|string|max:255',
            'contact_person_number' => 'required|string|max:20',
            'contact_person_designation' => 'required|string|max:255',
            'type_of_ownership' => 'required|string|in:Single Proprietorship,Partnership,Corporation',
            'nature_of_business' => 'required|string|max:255',
            'products_services' => 'required|string|max:255',
            'classification' => 'required|string|max:255',
            'tin' => 'required|string|max:15|unique:request_supplier,tin',
            'terms_and_conditions' => 'required|string',
            'filled_by' => 'required|string|max:255',
            'filled_designation' => 'required|string|max:255',
            'filled_date' => 'required|date_format:Y-m-d',
            'requirements_complete' => 'required|string|in:Yes,No',
            'remarks' => 'nullable|string',
            // 'attachments' => 'nullable|array',
            // 'attachments.*.attachment_name' => 'required|string|max:255',
            // 'attachments.*.file' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg',
            ...$this->storeApprovals(),
        ];
    }
}
