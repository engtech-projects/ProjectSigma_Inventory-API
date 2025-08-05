<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionMaterialReceivingItemAcceptSomeRequest extends FormRequest
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
            'remarks' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:1'],
        ];
    }
}
