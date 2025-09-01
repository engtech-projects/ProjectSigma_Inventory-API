<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestNcpoDetailedResource extends JsonResource
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
            'date' => $this->date,
            'ncpo_no' => $this->ncpo_no,
            'po_id' => $this->po_id,
            'justification' => $this->justification,
            'created_by' => $this->created_by,
            'items' => RequestNcpoItemResource::collection($this->ncpoItems),
        ];
    }
}
