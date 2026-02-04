<?php

namespace App\Http\Services;

use App\Enums\PurchaseOrderProcessingStatus;
use App\Enums\ServeStatus;
use App\Enums\ReceivingAcceptanceStatus;
use App\Models\RequestCanvassSummary;
use App\Models\RequestPurchaseOrder;
use App\Models\TransactionMaterialReceiving;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    protected $purchaseOrder;

    public function __construct(RequestPurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }
    public function createPurchaseOrderFromCanvass(RequestCanvassSummary $canvassSummary)
    {
        $canvassSummary->load([
            'priceQuotation.supplier',
            'priceQuotation.requestProcurement.requisitionSlip',
            'items.requisitionSlipItem.itemProfile',
            'items.priceQuotationItem',
        ]);
        $priceQuotation = $canvassSummary->priceQuotation;
        $requisitionSlip = $priceQuotation->requestProcurement->requisitionSlip;
        $supplier = $priceQuotation->supplier;
        $supplierData = [
            'id' => $supplier->id,
            'name' => $supplier->company_name,
            'address' => $supplier->company_address,
            'contact_number' => $supplier->company_contact_number,
        ];
        $items = $canvassSummary->items->map(function ($csItem) {
            $reqItem = $csItem->requisitionSlipItem;
            $pqItem = $csItem->priceQuotationItem;
            $canvassSummary = $csItem->requestCanvassSummary;
            $convertableUnits = $reqItem->itemProfile?->convertable_units ?? [];
            return [
                'id' => $reqItem->id ?? null,
                'item_id' => $csItem->item_id,
                'item_description' => $reqItem->item_description ?? '',
                'specification' => $reqItem->specification ?? '',
                'quantity' => $reqItem->quantity ?? 0,
                'uom' => $reqItem->uom_name ?? '',
                'uom_id' => $reqItem->unit ?? null,
                'remarks' => $canvassSummary->remarks ?? null,
                'convertable_units' => $convertableUnits,
                'actual_brand_purchase' => $pqItem?->actual_brand ?? '',
                'unit_price' => round((float)($csItem->unit_price ?? 0), 2),
                'net_amount' => round((float)($csItem->total_amount ?? 0), 2),
                'net_vat' => round((float)($csItem->net_vat ?? 0), 2),
                'input_vat' => round((float)($csItem->input_vat ?? 0), 2),
            ];
        })->toArray();
        $metadata = [
            'rs_number' => $requisitionSlip->reference_no ?? '',
            'equipment_no' => $requisitionSlip->equipment_no ?? '',
            'project_code' => $requisitionSlip->project_department_name ?? '',
            'processing_status' => PurchaseOrderProcessingStatus::PENDING->value,
            'created_by' => $canvassSummary->created_by,
            'terms_of_payment' => $canvassSummary->terms_of_payment ?? '',
            'availability' => $canvassSummary->availability ?? '',
            'delivery_terms' => $canvassSummary->delivery_terms ?? '',
            'total_amount' => $canvassSummary->grand_total_amount ?? 0,
            'supplier' => $supplierData,
            'items' => $items,
        ];
        return $this->purchaseOrder->create([
            'transaction_date' => now(),
            'po_number' => static::generatePoNumber(),
            'request_canvass_summary_id' => $canvassSummary->id,
            'name_on_receipt' => null,
            'delivered_to' => null,
            'processing_status' => PurchaseOrderProcessingStatus::PENDING,
            'metadata' => $metadata,
            'created_by' => $canvassSummary->created_by,
            'approvals' => $canvassSummary->approvals,
            'request_status' => $canvassSummary->request_status,
        ]);
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
                'requested_quantity'   => $item->requested_quantity,
                'quantity'             => $item->quantity,
                'uom_id'               => $item->uom_id,
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
    public static function setServed(RequestPurchaseOrder $requestPurchaseOrder)
    {
        $mrrItemsQuery = $requestPurchaseOrder->mrrNcpoItems();
        $allItemsAccepted = $mrrItemsQuery->get()->every(function ($item) {
            return $item->acceptance_status === ReceivingAcceptanceStatus::ACCEPTED->value;
        });
        if (!$allItemsAccepted) {
            throw new \Exception('Cannot mark Purchase Order as SERVED. Some items are still pending acceptance.');
        }
        $mrrItemsQuery->where('acceptance_status', ReceivingAcceptanceStatus::ACCEPTED->value)
            ->update([
                'serve_status' => ServeStatus::SERVED->value,
            ]);
        $requestPurchaseOrder->update([
            'processing_status' => PurchaseOrderProcessingStatus::SERVED->value,
        ]);
    }
}
