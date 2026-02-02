<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique("warehouse", "name")->ignore($this->route("resource"), 'id')->whereNull('deleted_at'),
            ],
            'location' => 'required|string|max:255',
            'owner_type' => 'required|in:Project,Department',
            'owner_id' => 'nullable|integer',
        ];
    }
}
