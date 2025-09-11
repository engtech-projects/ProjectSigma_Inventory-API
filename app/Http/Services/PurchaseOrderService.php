<?php

namespace App\Http\Services;

use App\Enums\PurchaseOrderProcessingStatus;
use App\Enums\ServeStatus;
use App\Models\RequestCanvassSummary;
use App\Models\RequestPurchaseOrder;
use App\Models\TransactionMaterialReceiving;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public static function createPurchaseOrderFromCanvass(RequestCanvassSummary $requestCanvassSummary)
    {
        return DB::transaction(function () use ($requestCanvassSummary) {
            return RequestPurchaseOrder::create([
                'transaction_date' => now(),
                'po_number' => static::generatePoNumber(),
                'request_canvass_summary_id' => $requestCanvassSummary->id,
                'name_on_receipt' => null,
                'delivered_to' => null,
                'processing_status' => PurchaseOrderProcessingStatus::PENDING,
                'metadata' => $requestCanvassSummary->metadata ?? [],
                'created_by' => $requestCanvassSummary->created_by,
                'request_status' => $requestCanvassSummary->request_status,
                'approvals' => $requestCanvassSummary->approvals,
            ]);
        });
    }

    public static function createMrrFromPurchaseOrder(RequestPurchaseOrder $requestPurchaseOrder): TransactionMaterialReceiving
    {
        return DB::transaction(function () use ($requestPurchaseOrder) {
            $mrr = new TransactionMaterialReceiving();
            $mrr->warehouse_id      = $requestPurchaseOrder->warehouse_id;
            $mrr->reference_no      = TransactionMaterialReceiving::generateNewMrrReferenceNumber();
            $mrr->supplier_id       = $requestPurchaseOrder->supplier_id;
            $mrr->reference         = null;
            $mrr->terms_of_payment  = $requestPurchaseOrder->terms_of_payment;
            $mrr->transaction_date  = $requestPurchaseOrder->transaction_date;
            $mrr->metadata          = [
                'is_purchase_order' => true,
                'po_id' => $requestPurchaseOrder->id,
                'rs_id' => $requestPurchaseOrder->rs_id,
            ];
            $mrr->save();
            $mappedItems = $requestPurchaseOrder->items->map(fn ($item) => [
                'transaction_material_receiving_id' => $mrr->id,
                'item_id'              => $item->item_id,
                'specification'        => $item->specification,
                'actual_brand_purchase' => $item->actual_brand_purchase,
                'requested_quantity'   => $item->quantity,
                'quantity'             => $item->quantity,
                'uom_id'               => $item->uom,
                'unit_price'           => $item->unit_price,
                'serve_status'         => ServeStatus::UNSERVED,
                'remarks'              => $item->remarks,
                'metadata'             => [
                    'po_id' => $requestPurchaseOrder->id,
                    'po_item_id' => $item->id,
                ],
            ]);
            $mrr->items()->createMany($mappedItems->toArray());
            return $mrr;
        });
    }
    private static function generatePoNumber()
    {
        $year = now()->year;
        $lastPO = RequestPurchaseOrder::orderBy('po_number', 'desc')
            ->first();
        $lastRefNo = $lastPO ? collect(explode('-', $lastPO->po_number))->last() : 0;
        $newNumber = str_pad($lastRefNo + 1, 6, '0', STR_PAD_LEFT);
        return "PO-{$year}-{$newNumber}";
    }

    public function applyFilters($query)
    {
        if (request()->has('rs_number')) {
            $query->where('rs_number', 'like', '%' . request()->input('rs_number') . '%');
        }
        if (request()->has('po_number')) {
            $query->where('po_number', request()->input('po_number'));
        }
        if (request()->has('transaction_date')) {
            $query->where('transaction_date', 'like', '%' . request()->input('transaction_date') . '%');
        }
        return $query;
    }

    public function getAllRequest()
    {
        $query = RequestPurchaseOrder::orderBy('created_at', 'DESC');
        $query = $this->applyFilters($query);
        return $query->paginate(10);
    }
}
