<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Exceptions\HttpResponseException;

class RejectWarehouseTransactionItemRequest extends FormRequest
{
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

        $metadata = $transaction->metadata ?? [];
        if (!isset($metadata['evaluated_by'])) {
            $metadata['evaluated_by'] = auth()->user()->id;
            $transaction->update(['metadata' => $metadata]);
        }

        return true;
    }

    public function rules()
    {
        return [
            'remarks' => 'required|string|max:500'
        ];
    }
}
