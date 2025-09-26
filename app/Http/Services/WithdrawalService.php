<?php

namespace App\Http\Services;

use App\Models\RequestWithdrawal;
use App\Models\WarehouseStockTransactions;
use App\Enums\StockTransactionTypes;
use Illuminate\Support\Facades\DB;
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
    public function withdrawItemsFromWarehouse($requestWithdrawalItems): void
    {
        DB::transaction(function () use ($requestWithdrawalItems) {
            // Group items by item_id (sum quantity)
            $groupedItems = collect($requestWithdrawalItems)
                ->groupBy('item_id')
                ->map(function ($items) {
                    return (object)[
                        'item_id' => $items->first()->item_id,
                        'uom_id' => $items->first()->uom_id,
                        'quantity' => $items->sum('quantity'),
                        'request_withdrawal_id' => $items->first()->request_withdrawal_id,
                    ];
                });
            // Process FIFO per unique item_id
            foreach ($groupedItems as $item) {
                $remainingQty = $item->quantity;
                [$deductions] = $this->deductStockFIFO($item, $remainingQty);
                // Creates StockOut per deduction (FIFO batches)
                foreach ($deductions as $deduction) {
                    $this->model->warehouseStockTransactions()->create([
                        'warehouse_id'   => $this->model->warehouse_id,
                        'type'           => StockTransactionTypes::STOCKOUT->value,
                        'parent_item_id' => $deduction['stock_in_id'],
                        'item_id'        => $item->item_id,
                        'quantity'       => $deduction['deducted_qty'],
                        'uom_id'         => $deduction['uom_id'],
                        'uom_conversion' => $deduction['uom_conversion'],
                        'metadata'       => [
                            'reason'                => 'Withdrawal',
                            'request_withdrawal_id' => $item->request_withdrawal_id,
                            'remaining_balance'     => $deduction['remaining_balance'],
                        ],
                    ]);
                }
            }
        });
    }
    /**
     * Deduct stock-in transactions using FIFO.
     *
     * @return array{array<int, array>, int} [deductions, totalDeducted]
     */
    private function deductStockFIFO($item, int $remainingQty): array
    {
        // Get all STOCKIN transactions (FIFO order)
        $stockIns = WarehouseStockTransactions::where('warehouse_id', $this->model->warehouse_id)
            ->where('item_id', $item->item_id)
            ->where('uom_id', $item->uom_id)
            ->where('type', StockTransactionTypes::STOCKIN->value)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();
        $deductions = [];
        $totalDeducted = 0;
        foreach ($stockIns as $stockIn) {
            if ($remainingQty <= 0) {
                break;
            }
            // Getting the remaining/available stocks in the warehouse
            $availableFromThisStockIn = $stockIn->remaining_stock;
            if ($availableFromThisStockIn <= 0) {
                continue; // skipping the empty
            }
            // Deduct from this batch
            $deductQty = min($remainingQty, $availableFromThisStockIn);
            $deductions[] = [
                'stock_in_id'    => $stockIn->id,
                'deducted_qty'   => $deductQty,
                'uom_id'         => $stockIn->uom_id,
                'uom_conversion' => $stockIn->uom_conversion,
                'remaining_balance'  => $availableFromThisStockIn - $deductQty,
            ];
            $totalDeducted += $deductQty;
            $remainingQty  -= $deductQty;
        }
        // If not enough stock, throw error
        if ($remainingQty > 0) {
            throw new Exception("Not enough stock available for Item ID: {$item->item_id}");
        }
        return [$deductions, $totalDeducted];
    }
}
