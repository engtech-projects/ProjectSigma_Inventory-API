<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestPurchaseOrderDetailedResource extends JsonResource
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
            'transaction_date' => $this->createdAtDateHuman,
            'po_number' => $this->po_number,
            'rs_number' => $this->requisitionSlip?->reference_no,
            'equipment_no' => $this->requisitionSlip?->equipment_no,
            'request_canvass_summary_id' => $this->request_canvass_summary_id,
            'project_code' => $this->requisitionSlip?->project_department_name,
            'name_on_receipt' => $this->name_on_receipt,
            'delivered_to' => $this->delivered_to,
            'metadata' => $this->metadata,
            'processing_status' => $this->processing_status,
            'created_by' => $this->created_by_user_name,
            'terms_of_payment' => $this->requestCanvassSummary?->terms_of_payment,
            'availability' => $this->requestCanvassSummary?->availability,
            'delivery_terms' => $this->requestCanvassSummary?->delivery_terms,
            'total_amount' => $this->requestCanvassSummary?->grand_total_amount,
            'supplier' => $this->whenLoaded('supplier', function () {
                return [
                    'id' => $this->supplier->id,
                    'name' => $this->supplier->company_name,
                    'address' => $this->supplier->company_address,
                    'contact_number' => $this->supplier->company_contact_number,
                ];
            }),
            'items' => $this->items,
            'ncpos' => $this->whenLoaded('ncpos'),
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
