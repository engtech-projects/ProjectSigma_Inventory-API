<?php

namespace App\Observers;

use App\Enums\StockTransactionTypes;
use App\Models\WarehouseStocksSummary;
use App\Models\WarehouseStockTransactions;

class WarehouseStockTransactionsObserver
{
    /**
     * Handle the WarehouseStockTransactions "created" event.
     */
    public function created(WarehouseStockTransactions $warehouseStockTransactions): void
    {
        // Get current warehouse stock summary or create a new one if it doesn't exist
        $warehouseSummary = WarehouseStocksSummary::where(
            [
                'warehouse_id' => $warehouseStockTransactions->warehouse_id,
                'item_id' => $warehouseStockTransactions->item_id,
            ]
        )->first();
        if(!$warehouseSummary) {
            $warehouseSummary = new WarehouseStocksSummary();
            $warehouseSummary->warehouse_id = $warehouseStockTransactions->warehouse_id;
            $warehouseSummary->item_id = $warehouseStockTransactions->item_id;
            $warehouseSummary->uom_id = $warehouseStockTransactions->uom_id;
            $warehouseSummary->metadata = [
                'last_transaction_id' => $warehouseStockTransactions->id
            ];
        }
        // get the new quantity based on the transaction type
        // and convert it to the summary's UOM if necessary
        $summaryUom = $warehouseSummary->uom_id;
        $quantity = $warehouseStockTransactions->qty;
        if($summaryUom != $warehouseStockTransactions->uom_id) {
            $quantity = $warehouseStockTransactions->getConvertedQuantity($summaryUom);
        }
        if($warehouseStockTransactions->type === StockTransactionTypes::STOCKIN) {
            $warehouseSummary->quantity += $quantity;
        } else {
            $warehouseSummary->quantity -= $quantity;
        }
        $warehouseSummary->metadata = [
            ...$warehouseSummary->metadata,
            'last_transaction_id' => $warehouseStockTransactions->id
        ];
        $warehouseSummary->updated_at = now();
        $warehouseSummary->save();
    }

    /**
     * Handle the WarehouseStockTransactions "updated" event.
     */
    public function updated(WarehouseStockTransactions $warehouseStockTransactions): void
    {
        //
    }

    /**
     * Handle the WarehouseStockTransactions "deleted" event.
     */
    public function deleted(WarehouseStockTransactions $warehouseStockTransactions): void
    {
        //
    }

    /**
     * Handle the WarehouseStockTransactions "restored" event.
     */
    public function restored(WarehouseStockTransactions $warehouseStockTransactions): void
    {
        //
    }

    /**
     * Handle the WarehouseStockTransactions "force deleted" event.
     */
    public function forceDeleted(WarehouseStockTransactions $warehouseStockTransactions): void
    {
        //
    }
}
