<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestTurnoverListingResource extends JsonResource
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
            'from_warehouse_id' => $this->fromWarehouse->name,
            'to_warehouse_id' => $this->toWarehouse->name,
            'request_status' => $this->request_status,
            'received_name' => $this->received_name,
            'received_date' => $this->formatReadableDate($this->received_date),
            'metadata' => $this->metadata,
            'created_at' => $this->formatReadableDate($this->created_at),
        ];
    }
}
