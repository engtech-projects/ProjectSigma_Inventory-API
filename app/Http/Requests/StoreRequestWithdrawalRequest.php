<?php

namespace App\Http\Requests;

use App\Http\Traits\HasApprovalValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\DB;
use App\Enums\FuelWithdrawal;
use App\Enums\OwnerType;

class StoreRequestWithdrawalRequest extends FormRequest
{
    use HasApprovalValidation;

    public function rules()
    {
        return [
            'date_time' => ['required', 'date'],
            'warehouse_id' => ['required', 'exists:setup_warehouses,id'],
            'chargeable_type' => ['required', new Enum(OwnerType::class)],
            'chargeable_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($this->chargeable_type === OwnerType::PROJECT) {
                        if (!DB::table('setup_projects')->where('id', $value)->exists()) {
                            $fail('Invalid Project selected.');
                        }
                    } elseif ($this->chargeable_type === OwnerType::DEPARTMENT) {
                        if (!DB::table('setup_departments')->where('id', $value)->exists()) {
                            $fail('Invalid Department selected.');
                        }
                    }
                },
            ],
            // 'created_by' => ['nullable', 'exists:users,id'], // Exists in users list
            // Tentative for future use/ for further discussion if auto generated
            'reference_no' => ['nullable', 'string'], // Tentative for future use/ for further discussion if auto generated
            'equipment_no' => ['nullable', 'string'],
            'smr' => ['nullable', 'string'],
            'fuel' => [
                'nullable',
                'string',
                new Enum(FuelWithdrawal::class),
            ],
            // 'metadata' => ['nullable', 'array'],
            'items' => ['required', 'array'],
            'items.*.item_id' => [
                'required',
                'exists:item_profile,id',
                function ($attribute, $value, $fail) {
                    $warehouseId = $this->warehouse_id;
                    $stock = DB::table('warehouse_stock_transactions')
                        ->where('warehouse_id', $warehouseId)
                        ->where('item_id', $value)
                        ->value('quantity');

                    if (is_null($stock)) {
                        $fail("Item not available in selected warehouse.");
                    }
                }
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $itemId = $this->items[$index]['item_id'] ?? null;
                    $warehouseId = $this->warehouse_id;

                    $stock = DB::table('warehouse_stock_transactions')
                        ->where('warehouse_id', $warehouseId)
                        ->where('item_id', $itemId)
                        ->value('quantity');

                    if ($stock !== null && $value > $stock) {
                        $fail("Quantity exceeds available stock.");
                    }
                }
            ],
            'items.*.uom_id' => ['required', 'exists:setup_uom,id'],
            'items.*.purpose_of_withdrawal' => ['nullable', 'string'],
            ...$this->storeApprovals(),
        ];
    }
}
