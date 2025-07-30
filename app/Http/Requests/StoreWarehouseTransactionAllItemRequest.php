<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreWarehouseTransactionAllItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $resource = $this->route('resource');
        if (!$resource) {
            return false;
        }
        $transaction = $resource->transaction;
        $response = Gate::inspect('isEvaluator', $transaction);
        if ($response->denied()) {
            throw new HttpResponseException(
                response()->json([
                    'message' => $response->message()
                ], 403)
            );
        }
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
            'specification' => 'nullable|string|max:255',
            'actual_brand_purchase' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unit_price.required' => 'The unit price is required.',
            'actual_brand_purchase.required' => 'The actual brand purchase is required.',
        ];

    }
}
