<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectWarehouseTransactionItemRequest extends FormRequest
{
    public function rules()
    {
        return [
            'remarks' => 'required|string|max:500'
        ];
    }
}
