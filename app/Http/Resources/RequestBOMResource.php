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
            'items' => $this->item_summary,
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
