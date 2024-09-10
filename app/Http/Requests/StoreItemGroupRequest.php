<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemGroupRequest extends FormRequest
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
        if (gettype($this->sub_groups) == "string") {
            $this->merge([
                "sub_groups" => json_decode($this->sub_groups, true)
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique("setup_item_groups", "name")->withoutTrashed()
            ],
            'sub_groups' => "required|array",
            'sub_groups.*' => [
                'required',
                'string',
                'max:255',
            ],

        ];
    }
}
