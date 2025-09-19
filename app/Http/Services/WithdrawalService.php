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
    /**
     * Withdraw items from warehouse using FIFO logic.
     *
     */
    public function withdrawItemsFromWarehouse($requestWithdrawalItems)
    {
        DB::transaction(function () use ($requestWithdrawalItems) {
            Log::info("Processing withdrawal items [{$requestWithdrawalItems}]");
            foreach ($requestWithdrawalItems as $item) {
                $remainingQty   = $item['quantity'];
                $totalAvailable = $this->getTotalAvailable($item['item_id']);
                // Check if stock is sufficient
                if ($totalAvailable < $remainingQty) {
                    throw new Exception("Not enough stock available for Item ID: {$item['item_id']}");
                }
                // Deduct FIFO
                [$deductions, $totalDeducted] = $this->deductStockFIFO($item['item_id'], $remainingQty);
                // Record Stock Out transaction
                $this->model->warehouseStockTransactions()->create([
                    'warehouse_id'   => $this->model->warehouse_id,
                    'type'           => StockTransactionTypes::STOCKOUT->value,
                    'parent_item_id' => $item['item_id'],
                    'item_id'        => $item['item_id'],
                    'quantity'       => $totalDeducted,
                    'uom_id'         => $deductions[0]['uom_id'] ?? null,
                    'uom_conversion' => $deductions[0]['uom_conversion'] ?? null,
                    'metadata'       => [
                        'reason'                => 'Withdrawal',
                        'request_withdrawal_id' => $item['request_withdrawal_id'],
                        'deductions'            => $deductions,
                    ],
                ]);
            }
        });
    }
    /**
     * Get total available stock for an item.
     */
    private function getTotalAvailable(int $itemId): int
    {
        return WarehouseStockTransactions::where('item_id', $itemId)
            ->where('warehouse_id', $this->model->warehouse_id)
            ->where('type', StockTransactionTypes::STOCKIN->value)
            ->where('quantity', '>', 0)
            ->sum('quantity');
    }
    /**
     * Deduct stock-in transactions using FIFO.
     *
     * @return array{array<int, array>, int} [deductions, totalDeducted]
     */
    private function deductStockFIFO(int $itemId, int $remainingQty): array
    {
        $stockIns = WarehouseStockTransactions::where('item_id', $itemId)
            ->where('warehouse_id', $this->model->warehouse_id)
            ->where('type', StockTransactionTypes::STOCKIN->value)
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();
        $deductions   = [];
        $totalDeducted = 0;
        foreach ($stockIns as $stockIn) {
            if ($remainingQty <= 0) {
                break;
            }
            $deductQty = min($remainingQty, $stockIn->quantity);
            $stockIn->quantity -= $deductQty;
            $stockIn->save();
            $deductions[] = [
                'stock_in_id'    => $stockIn->id,
                'deducted_qty'   => $deductQty,
                'uom_id'         => $stockIn->uom_id,
                'uom_conversion' => $stockIn->uom_conversion,
            ];
            $totalDeducted += $deductQty;
            $remainingQty  -= $deductQty;
        }
        return [$deductions, $totalDeducted];
    }
}
