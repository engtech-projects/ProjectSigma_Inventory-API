<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestSupplierUpload extends FormRequest
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
            'request_supplier_id' => [
                'required',
                'integer',
                'exists:request_supplier,id'
            ],
            'attachments' => [
                'required',
                'array',
            ],
            'attachments.*.attachment_name' => [
                'required',
                'string',
                'max:255',
            ],
            'attachments.*.file' => [
                'required',
                'file',
                'max:10000',
                'mimes:pdf,doc,docx,jpg,png,jpeg',
            ],
        ];
    }
}
