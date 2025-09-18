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
            'transaction_date' => $this->createdAtDateHuman,
            'po_number' => $this->po_number,
            'rs_number' => $this->requisition_slip?->reference_no,
            'equipment_no' => $this->requisition_slip?->equipment_no,
            'processing_status' => $this->processing_status,
            'project_code' => $this->requisition_slip?->project_department_name,
            'terms_of_payment' => $this->requestCanvassSummary?->terms_of_payment,
            'availability' => $this->requestCanvassSummary?->availability,
            'delivery_terms' => $this->requestCanvassSummary?->delivery_terms,
            'original_total' => number_format($this->ncpos->last()?->original_total ?? $this->requestCanvassSummary?->grand_total_amount ?? 0, 2),
            'new_po_total' => number_format($this->ncpos->last()?->new_po_total ?? 0, 2),
            'items_count' => $this->requestCanvassSummary?->items?->count() ?? 0,
            'name_on_receipt' => $this->name_on_receipt,
            'delivered_to' => $this->delivered_to,
            'items' => $itemsWithChanges->toArray(),
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
