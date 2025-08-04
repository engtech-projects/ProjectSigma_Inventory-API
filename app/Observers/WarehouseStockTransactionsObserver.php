<?php

namespace App\Observers;

use App\Enums\StockTransactionTypes;
use App\Models\WarehouseStocksSummary;
use App\Models\WarehouseStockTransactions;
use Illuminate\Support\Facades\DB;

class WarehouseStockTransactionsObserver
{
    /**
     * Handle the WarehouseStockTransactions "created" event.
     */
    public function created(WarehouseStockTransactions $warehouseStockTransactions): void
    {
        $currentWarehouseStockSummary = WarehouseStocksSummary::where(
            [
                'warehouse_id' => $warehouseStockTransactions->warehouse_id,
                'item_id' => $warehouseStockTransactions->item_id,
            ]
        )->first();
        if(!$currentWarehouseStockSummary) {
            $currentWarehouseStockSummary = new WarehouseStocksSummary();
            $currentWarehouseStockSummary->warehouse_id = $warehouseStockTransactions->warehouse_id;
            $currentWarehouseStockSummary->item_id = $warehouseStockTransactions->item_id;
            $currentWarehouseStockSummary->uom_id = $warehouseStockTransactions->uom_id;
        }
        $summaryUom = $currentWarehouseStockSummary->uom_id;
        if($summaryUom != $warehouseStockTransactions->uom_id) {
            $conversion = $warehouseStockTransactions->item-> uomConversion($warehouseStockTransactions->uom_id, $summaryUom);
            $currentWarehouseStockSummary->stock_in += $warehouseStockTransactions->qty * $conversion;
        } else {
            $currentWarehouseStockSummary->stock_in += $warehouseStockTransactions->qty;
        }

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
