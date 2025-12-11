<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemGroupRequest extends FormRequest
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
                Rule::unique("setup_item_groups", "name")->ignore($this->route("resource"), 'id')->whereNull('deleted_at')
            ],
            'sub_groups' => "required|array|min:1",
            'sub_groups.*' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
