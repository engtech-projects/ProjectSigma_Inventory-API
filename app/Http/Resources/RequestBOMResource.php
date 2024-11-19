<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestBOMResource extends JsonResource
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
            'assignment_id' => $this->assignment_id,
            'assignment_type' => $this->assignment_type,
            'effectivity' => $this->effectivity,
            'created_by' => $this->created_by,
            'request_status' => $this->request_status,
            'item_summary' => $this->details->map(function ($detail) {
                return [
                    'request_bom_id' => $detail->request_bom_id,
                    'item_id' => $detail->item_id,
                    'item_summary' => $detail->item_summary,
                    'uom_id' => $detail->uom_id,
                    'unit' => $detail->uom->name,
                    'price' => $detail->unit_price,
                    'quantity' => $detail->quantity,
                    'amount' => number_format($detail->unit_price * $detail->quantity, 2),
                ];
            })->toArray(),
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
