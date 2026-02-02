<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartialReturnBorrowTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_time_returned' => ['required', 'date'],
            'returned_by' => ['required', 'string', 'max:255'],
            'received_by' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:borrow_transaction_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.remarks' => ['nullable', 'string'],
        ];
    }
}
