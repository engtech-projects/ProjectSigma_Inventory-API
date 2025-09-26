<?php

namespace App\Http\Requests;

use App\Http\Traits\HasApprovalValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\StockTransactionTypes;
use Illuminate\Support\Facades\DB;
use App\Enums\FuelWithdrawal;
use App\Enums\OwnerType;
use App\Enums\RequestStatuses;
use App\Models\WarehouseStockTransactions;
use App\Models\RequestWithdrawalItem;

class StoreRequestWithdrawalRequest extends FormRequest
{
    use HasApprovalValidation;

    public function rules()
    {
        return [
            'date_time' => ['required', 'date'],
            'warehouse_id' => ['bail', 'required', 'exists:setup_warehouses,id'],
            'chargeable_type' => ['required', new Enum(OwnerType::class)],
            'chargeable_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $type = OwnerType::tryFrom($this->input('chargeable_type'));
                    if (!$type) {
                        return $fail('Invalid chargeable type.');
                    }
                    if ($type === OwnerType::PROJECT) {
                        if (!DB::table('setup_projects')->where('id', $value)->exists()) {
                            $fail('Invalid Project selected.');
                        }
                    } elseif ($type === OwnerType::DEPARTMENT) {
                        if (!DB::table('setup_departments')->where('id', $value)->exists()) {
                            $fail('Invalid Department selected.');
                        }
                    }
                },
            ],
            'equipment_no' => ['nullable', 'string'],
            'smr' => ['nullable', 'string'],
            'fuel' => [
                'nullable',
                'string',
                new Enum(FuelWithdrawal::class),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:item_profile,id'],
            'items.*.quantity' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $itemId = $this->items[$index]['item_id'] ?? null;
                    $warehouseId = $this->warehouse_id;
                    $uom_id = $this->uom_id;
                    // STOCKIN
                    $stockIns = WarehouseStockTransactions::where('warehouse_id', $warehouseId)
                        ->where('item_id', $itemId)
                        ->where('uom_id', $uom_id)
                        ->where('type', StockTransactionTypes::STOCKIN->value)
                        ->sum('quantity');
                    // STOCKOUT
                    $stockOuts = WarehouseStockTransactions::where('warehouse_id', $warehouseId)
                        ->where('item_id', $itemId)
                        ->where('uom_id', $uom_id)
                        ->where('type', StockTransactionTypes::STOCKOUT->value)
                        ->sum('quantity');
                    // Requested Withdrawal (already pending, from RequestWithdrawalItem)
                    $requestedWithdrawal = RequestWithdrawalItem::whereHas('requestWithdrawal', function ($q) use ($warehouseId) {
                        $q->where('request_status', RequestStatuses::PENDING->value);
                        $q->where('warehouse_id', $warehouseId);
                    })
                    ->where('item_id', $itemId)
                    ->sum('quantity');
                    // Totals
                    $availableStocks = $stockIns - $stockOuts; // total physically available
                    $availableStocksForRequest = $availableStocks - $requestedWithdrawal; // left after pending withdrawals
                    // Validation
                    if($availableStocks <= 0) {
                        $fail("The item has no available stock at the moment.");
                    } elseif($value > $availableStocksForRequest) {
                        $fail("Quantity exceeds maximum withdrawal requests.");
                    } elseif($value > $availableStocks) {
                        $fail("Quantity exceeds available stock.");
                    }
                }
            ],
            'items.*.uom_id' => ['required', 'exists:setup_uom,id'],
            'items.*.purpose_of_withdrawal' => ['nullable', 'string', 'max:500'],
            ...$this->storeApprovals(),
        ];
    }
}
