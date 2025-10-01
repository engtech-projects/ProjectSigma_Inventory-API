<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestNcpoDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->formatReadableDate($this->date),
            'ncpo_no' => $this->ncpo_no,
            'po_number' => $this->purchaseOrder?->po_number,
            'po_date' => $this->purchaseOrder?->transaction_date ? $this->formatReadableDate($this->purchaseOrder->transaction_date) : null,
            'project_code' => $this->purchaseOrder?->requisitionSlip?->project_department_name,
            'equipment_number' => $this->purchaseOrder?->requisitionSlip?->equipment_no,
            'justification' => $this->justification,
            'created_by' => $this->created_by,
            'total_amount' => $this->purchaseOrder?->requestCanvassSummary?->grand_total_amount
                ? number_format($this->purchaseOrder->requestCanvassSummary->grand_total_amount, 2)
                : '0.00',
            'new_po_total' => number_format($this->new_po_total, 2),
            'original' => [
                'items'    => $this->purchaseOrder?->items,
            ],
            'changed' => [
                'items' => RequestNcpoItemResource::collection($this->items),
            ],
            "approvals" => new ApprovalAttributeResource($this->approvals),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
