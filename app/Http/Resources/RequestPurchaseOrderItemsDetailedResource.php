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
            'id' => $this->id,
            'date' => $this->transaction_date,
            'po_number' => $this->po_number,
            'rs_number' => $this->requisition_slip?->reference_no,
            'equipment_no' => $this->requisition_slip?->equipment_no,
            'processing_status' => $this->processing_status,
            'project_code' => $this->requisition_slip?->project_department_name,
            'payment_terms' => $this->requestCanvassSummary?->terms_of_payment,
            'availability' => $this->requestCanvassSummary?->availability,
            'delivery_terms' => $this->requestCanvassSummary?->delivery_terms,
            'items_count' => $this->requestCanvassSummary?->items?->count() ?? 0,
            'name_on_receipt' => $this->name_on_receipt,
            'delivered_to' => $this->delivered_to,
            'items' => $itemsWithChanges->toArray(),
        ];
    }
}
