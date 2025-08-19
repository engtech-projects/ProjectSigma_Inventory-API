<?php

namespace App\Http\Requests;

use App\Models\PriceQuotationItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Traits\HasApprovalValidation;

class StoreCanvassSummary extends FormRequest
{
    use HasApprovalValidation;
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
            'price_quotation_id' => ['required', 'exists:price_quotations,id'],
            'items' => ['required', 'array'],
            'items.*.item_id' => [
                'required',
                Rule::exists('item_profile', 'id')->where('is_approved', 1),
                function ($attribute, $value, $fail) {
                    $priceQuotationId = $this->input('price_quotation_id');
                    if (!$this->itemExistsInQuotation($priceQuotationId, $value)) {
                        $fail("Item ID {$value} is not listed under the given price quotation.");
                    }
                }
            ],
            'terms_of_payment' => 'required|string',
            'availability' => 'required|string',
            'delivery_terms' => 'required|string',
            'remarks' => 'nullable|string',
            ...$this->storeApprovals(),
        ];
    }

    protected function itemExistsInQuotation($priceQuotationId, $itemId)
    {
        return PriceQuotationItem::where('price_quotation_id', $priceQuotationId)
            ->where('item_id', $itemId)
            ->exists();
    }
}
