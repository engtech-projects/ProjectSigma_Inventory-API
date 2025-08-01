<?php

namespace App\Http\Requests;

use App\Models\RequestStockItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePriceQuotationRequest extends FormRequest
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
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('request_supplier', 'id')->where('request_status', 'Approved')
            ],
            'items' => ['required', 'array'],
            'items.*.item_id' => [
                'required',
                Rule::exists('item_profile', 'id')->where('is_approved', 1),
                function ($attribute, $value, $fail) {
                    if (!$this->validateItemBelongsToProcurement($value)) {
                        $fail('The selected item does not belong to this procurement request.');
                    }
                }
            ],
            'items.*.actual_brand' => ['nullable', 'string'],
            'items.*.unit_price' => ['nullable', 'numeric'],
            'items.*.remarks_during_canvass' => ['nullable', 'string'],
        ];
    }
    protected function validateItemBelongsToProcurement($itemId): bool
    {
        $procurementId = $this->route('requestProcurement')->id;
        // Check if the item exists in any request stock that belongs to this requisition slip
        return RequestStockItem::where('item_id', $itemId)
            ->whereHas('requestStock.requestProcurement', function ($query) use ($procurementId) {
                $query->where('id', $procurementId);
            })
            ->exists();
    }
}
