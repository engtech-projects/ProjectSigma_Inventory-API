<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestTurnoverDetailedResource extends JsonResource
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
            'date' => $this->formatReadableDate($this->date),
            'from' => $this->from->name ?? $this->from->department_name ?? $this->from->project_code,
            'to' => $this->to->name ?? $this->to->department_name ?? $this->to->project_code,
            'created_by' => $this->createdBy->name,
            'received_date' => $this->formatReadableDate($this->received_date),
            'received_name' => $this->received_name,
            'metadata' => $this->metadata,
            'items' => RequestTurnoverItemResource::collection($this->whenLoaded('items')),
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
