<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialReceivingListingResource extends JsonResource
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
            'warehouse' => $this->warehouse_name,
            'transaction_date' => $this->transaction_date,
            'transaction_type' => $this->transaction_type,
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
