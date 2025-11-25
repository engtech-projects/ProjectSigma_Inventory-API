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
            'date' => $this->date,
            'approval_status' => $this->approval_status,
            'received_date' => $this->received_date,
            'received_name' => $this->received_name,
            'created_at_human' => $this->createdAtDateHuman,
        ];
    }
}
