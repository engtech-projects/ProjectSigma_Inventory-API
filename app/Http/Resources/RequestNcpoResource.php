<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestNcpoResource extends JsonResource
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
            'po_id' => $this->po_id,
            'date' => $this->date,
            'ncpo_no' => $this->ncpo_no,
            'justification' => $this->justification,
            'created_at' => $this->createdAtDateHuman,
            'new_po_total' => $this->new_po_total,
            'items' => RequestNcpoItemResource::collection($this->items),
        ];
    }
}
