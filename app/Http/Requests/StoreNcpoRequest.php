<?php

namespace App\Http\Requests;

use App\Models\RequestCanvassSummaryItems;
use App\Models\RequestPurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\HasApprovalValidation;
use Illuminate\Validation\Rule;

class StoreNcpoRequest extends FormRequest
{
    use HasApprovalValidation;

    protected $validItems;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($poId = $this->input('po_id')) {
            $canvassSummaryId = RequestPurchaseOrder::where('id', $poId)->value('request_canvass_summary_id');
            $this->validItems = $canvassSummaryId
                ? RequestCanvassSummaryItems::where('request_canvass_summary_id', $canvassSummaryId)->pluck('item_id')->all()
                : [];
        }
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'po_id' => 'required|exists:request_purchase_orders,id',
            'justification' => 'required|string',
            'approvals' => 'nullable|array',
            'items' => 'required|array',
            'items.*.item_id' => [
                'required',
                Rule::exists('item_profile', 'id')->where('is_approved', 1),
                fn ($attr, $val, $fail) => !in_array($val, $this->validItems ?? [], true)
                    ? $fail("Item ID $val does not belong to the canvass summary items.")
                    : null
            ],
            'items.*.changed_qty' => 'nullable|numeric',
            'items.*.changed_uom_id' => 'nullable|exists:setup_uom,id',
            'items.*.changed_unit_price' => 'nullable|numeric',
            'items.*.changed_supplier_id' => 'nullable|exists:request_supplier,id',
            'items.*.changed_item_description' => 'nullable|string',
            'items.*.changed_specification' => 'nullable|string',
            'items.*.changed_brand' => 'nullable|string',
            'items.*.cancel_item' => 'nullable|boolean',
            ...$this->storeApprovals(),
        ];
    }
}
