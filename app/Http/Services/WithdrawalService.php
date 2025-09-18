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
            Log::info($requestWithdrawalItems);
            //Logic of FIFO
            // When Request Fully Approved. - Done
            // deduct Stocks in warehouseStockTransactions and add withdrawal transaction to warehouse logs
            // Implement FIFO
            // and add metadata to connect Withdrawal request to warehouse transaction
            // add display info for request
            foreach ($requestWithdrawalItems as $requestWithdrawalItem) {
                $remainingQty = $requestWithdrawalItem['quantity'];
                // Get stock-in transactions (FIFO order)
                $stockIns = WarehouseStockTransactions::where('item_id', $requestWithdrawalItem['item_id'])
                    ->where('type', StockTransactionTypes::STOCKIN->value)
                    ->where('quantity', '>', 0) // only stock with balance left
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();
                foreach ($stockIns as $stockIn) {
                    if ($remainingQty <= 0) break;
                    $deductQty = min($remainingQty, $stockIn->quantity);
                    // Reduce from stock-in balance
                    $stockIn->quantity -= $deductQty;
                    $stockIn->save();
                    $this->model->warehouseStockTransactions()->create([
                        'warehouse_id' => $requestWithdrawalItem->requestWithdrawal->warehouse_id,
                        'type' => StockTransactionTypes::STOCKOUT->value,
                        'item_id' => $stockIn->item_id,
                        'quantity' => $stockIn->quantity,
                        'uom_id' => $stockIn->uom_id,
                        'parent_item_id' => $stockIn->id,
                        'metadata' => [
                            'reason' => 'Withdrawal',
                            'request_withdrawal_id' => $requestWithdrawalItem['request_withdrawal_id'],
                        ]
                    ]);
                    $remainingQty -= $deductQty;
                }
                // If stock not enough, rollback transaction
                if ($remainingQty > 0) {
                    throw new Exception("Not enough stock available for Item ID: {$requestWithdrawalItem['item_id']}");
                }
            }
            return response()->json([
                'message' => 'Successfully accepted.',
                'success' => true,
                'data' => $requestWithdrawalItems,
            ]);
        });
    }
}
