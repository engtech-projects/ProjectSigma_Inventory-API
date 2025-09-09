<?php

namespace App\Http\Resources;

use App\Http\Services\NcpoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestPurchaseOrderItemsDetailedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $ncpoService = app(NcpoService::class);

        $itemsWithChanges = $ncpoService->getItemsWithChanges($this->resource);

        return [
            'id'                => $this->id,
            'date'              => $this->transaction_date,
            'po_number'         => $this->po_number,
            'rs_number'         => $this->requisition_slip?->reference_no,
            'supplier'          => $ncpoService->getSupplierDetails($this->resource),
            'equipment_no'      => $this->requisition_slip?->equipment_no,
            'processing_status' => $this->processing_status,
            'project_code'      => $this->requisition_slip?->project_department_name,
            'payment_terms'     => $this->requestCanvassSummary?->terms_of_payment,
            'availability'      => $this->requestCanvassSummary?->availability,
            'delivery_terms'    => $this->requestCanvassSummary?->delivery_terms,
            'original_total'    => $this->ncpos->last()?->original_total
                                    ?? $this->requestCanvassSummary?->grand_total_amount
                                    ?? 0,
            'new_po_total'      => $this->ncpos->last()?->new_po_total ?? 0,
            'items_count'       => $this->requestCanvassSummary?->items?->count() ?? 0,
            'items'             => $itemsWithChanges->map(function ($item) {
                return [
                    'item_id' => $item['item_id'],
                    'current' => $item['current'],
                ];
            })->toArray(),
        ];
    }
}
