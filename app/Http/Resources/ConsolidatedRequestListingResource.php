<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsolidatedRequestListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "reference_no" => $this->reference_no,
            "purpose" => $this->purpose,
            "consolidated_by" => $this->consolidated_by,
            "date_consolidated" => $this->formatReadableDate($this->date_consolidated),
            "status" => $this->status,
            "remarks" => $this->remarks,
        ];
    }
}
