<?php

namespace App\Http\Services;

use App\Enums\PurchaseOrderProcessingStatus;
use App\Models\RequestCanvassSummary;
use App\Models\RequestPurchaseOrder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    protected $model;
    public function __construct(RequestCanvassSummary $model)
    {
        $this->model = $model;
    }
    public function createPurchaseOrderFromCanvass(RequestCanvassSummary $requestCanvassSummary)
    {
        return DB::transaction(function () use ($requestCanvassSummary) {
            return RequestPurchaseOrder::create([
                'transaction_date' => now(),
                'po_number' => $this->generatePoNumber(),
                'request_canvass_summary_id' => $requestCanvassSummary->id,
                'name_on_receipt' => null,
                'delivered_to' => null,
                'processing_status' => PurchaseOrderProcessingStatus::PENDING,
                'metadata' => $requestCanvassSummary->metadata ?? [],
                'created_by' => $requestCanvassSummary->created_by,
                'request_status' => $requestCanvassSummary->request_status,
                'approvals' => $requestCanvassSummary->approvals,
            ]);
        }, 5); // retry 5 times
    }

    private function generatePoNumber(): string
    {
        $year = now()->year;
        $initials = 'NJTT'; // static for now
        $prefix = "PO-{$year}-{$initials}";

        $latest = RequestPurchaseOrder::query()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $latestSeries = 0;

        if ($latest && !empty($latest->po_number)) {
            // Extract the last 4-digit series from po_number
            preg_match('/(\d{4})$/', $latest->po_number, $matches);
            $latestSeries = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        $newSeries = str_pad($latestSeries + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$newSeries}";
    }
}
