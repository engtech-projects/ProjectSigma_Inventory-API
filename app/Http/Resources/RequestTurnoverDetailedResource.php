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
            'from_warehouse' => $this->fromWarehouse->name,
            'to_warehouse' => $this->toWarehouse->name,
            'requested_by' => $this->requestedBy->name,
            'approved_by' => $this->approved_by,
            'received_date' => $this->formatReadableDate($this->received_date),
            'received_name' => $this->received_name,
            'approval_status' => $this->approval_status,
            'remarks' => $this->remarks,
            'metadata' => $this->metadata,
            'items' => RequestTurnoverItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->formatReadableDate($this->created_at),
            'updated_at' => $this->formatReadableDate($this->updated_at),
        ];
    }
}
