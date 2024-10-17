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
        return[
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'transaction_type' => $this->transaction_type,
            'charging_type' => $this->charging_type,
            'charging_id' => $this->charging_id,
            'items' => WarehouseTransactionItemResource::collection($this->items),
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
