<?php

namespace App\Http\Services;

use App\Enums\PurchaseOrderProcessingStatus;
use App\Models\RequestCanvassSummary;
use App\Models\RequestPurchaseOrder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public static function createPurchaseOrderFromCanvass(RequestCanvassSummary $requestCanvassSummary)
    {
        return DB::transaction(function () use ($requestCanvassSummary) {
            $terms = strtolower($requestCanvassSummary->terms_of_payment ?? '');
            $processingStatus = $terms === strtolower('Prepayment in Full')
                ? PurchaseOrderProcessingStatus::PREPAYMENT
                : PurchaseOrderProcessingStatus::PENDING;
            return RequestPurchaseOrder::create([
                'transaction_date' => now(),
                'po_number' => static::generatePoNumber(),
                'request_canvass_summary_id' => $requestCanvassSummary->id,
                'name_on_receipt' => null,
                'delivered_to' => null,
                'processing_status' => $processingStatus,
                'metadata' => $requestCanvassSummary->metadata ?? [],
                'created_by' => $requestCanvassSummary->created_by,
                'request_status' => $requestCanvassSummary->request_status,
                'approvals' => $requestCanvassSummary->approvals,
            ]);
        });
    }

    private static function generatePoNumber()
    {
        $year = now()->year;
        $lastPO = RequestPurchaseOrder::orderByRaw('SUBSTRING_INDEX(po_number, \'-\', -1) DESC')
            ->first();
        $lastRefNo = $lastPO ? collect(explode('-', $lastPO->po_number))->last() : 0;
        $newNumber = str_pad($lastRefNo + 1, 6, '0', STR_PAD_LEFT);
        return "PO-{$year}-{$newNumber}";
    }
}
