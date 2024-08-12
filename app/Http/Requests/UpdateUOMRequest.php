<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUOMRequest extends FormRequest
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
            'group_id' => 'exists:setup_uom_group,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique("setup_uom", "name")->ignore($this->route("resource"), 'id')->whereNull('deleted_at'),
            ],
            'symbol' => 'required|string|max:10|unique:setup_uom,symbol',
            'conversion' => 'nullable|numeric',
            'is_standard' => 'boolean'
        ];
    }
}
