<?php

namespace App\Http\Services;

use App\Models\RequestWithdrawal;
use App\Models\WarehouseStockTransactions;
use App\Enums\StockTransactionTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class WithdrawalService
{
    protected $model;
    public function __construct(RequestWithdrawal $model)
    {
        $this->model = $model;
    }
    public function withdrawItemsFromWarehouse($requestWithdrawalItems)
    {
        DB::transaction(function () use ($requestWithdrawalItems) {
            foreach ($requestWithdrawalItems as $requestWithdrawalItem) {
                $remainingQty = $requestWithdrawalItem['quantity'];
                // Check total available stock for this item
                $totalAvailable = WarehouseStockTransactions::where('item_id', $requestWithdrawalItem['item_id'])
                    ->where('type', StockTransactionTypes::STOCKIN->value)
                    ->where('quantity', '>', 0)
                    ->sum('quantity');
                if ($totalAvailable < $remainingQty) {
                    throw new Exception("Not enough stock available for Item ID: {$requestWithdrawalItem['item_id']}");
                }
                // Get stock-in transactions in FIFO order
                $stockIns = WarehouseStockTransactions::where('item_id', $requestWithdrawalItem['item_id'])
                    ->where('type', StockTransactionTypes::STOCKIN->value)
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();
                $deductions = []; // track FIFO deductions
                $totalDeducted = 0;
                foreach ($stockIns as $stockIn) {
                    if ($remainingQty <= 0) break;
                    $deductQty = min($remainingQty, $stockIn->quantity);
                    // Reduce from stock-in balance
                    $stockIn->quantity -= $deductQty;
                    $stockIn->save();
                    $deductions[] = [
                        'stock_in_id' => $stockIn->id,
                        'deducted_qty' => $deductQty,
                        'uom_id' => $stockIn->uom_id,
                        'uom_conversion' => $stockIn->uom_conversion,
                    ];
                    $totalDeducted += $deductQty;
                    $remainingQty -= $deductQty;
                }
                $this->model->warehouseStockTransactions()->create([
                    'warehouse_id' => $requestWithdrawalItem->requestWithdrawal->warehouse_id,
                    'type' => StockTransactionTypes::STOCKOUT->value,
                    'parent_item_id' => $requestWithdrawalItem['parent_item_id'],
                    'item_id' => $requestWithdrawalItem['item_id'],
                    'quantity' => $totalDeducted,
                    'uom_id' => $deductions[0]['uom_id'] ?? null,
                    'uom_conversion' => $deductions[0]['uom_conversion'] ?? null,
                    'metadata' => [
                        'reason' => 'Withdrawal',
                        'request_withdrawal_id' => $requestWithdrawalItem['request_withdrawal_id'],
                        'deductions' => $deductions,
                    ],
                ]);
            }
        });
    }

}
