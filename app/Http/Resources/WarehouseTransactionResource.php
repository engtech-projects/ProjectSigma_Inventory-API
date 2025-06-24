<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseTransactionResource extends JsonResource
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
            'reference_no' => $this->reference_no,
            'reference' => $this->rs_reference_no,
            'transaction_date' => $this->transaction_date,
            'transaction_type' => $this->transaction_type,
            'metadata' => $this->metadata,
            'charging_type' => $this->charging_type,
            'charging_id' => $this->charging_id,
            'project_code' => $this->project_code,
            'created_by' => $this->created_by,
            'request_status' => $this->request_status,
            'items' => WarehouseTransactionItemResource::collection($this->items),
            'warehouse' => $this->warehouse_name,
            'supplier' => $this->supplier_company_name,
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
