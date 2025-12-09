<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Gate;

class TransactionMaterialReceivingItemAcceptAllRequest extends FormRequest
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
        $transaction = $resource->transactionMaterialReceiving;
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
            //
        ];
    }
}
