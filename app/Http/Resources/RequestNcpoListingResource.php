<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestNcpoListingResource extends JsonResource
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
            'date' => $this->formatReadableDateAttribute($this->date),
            'ncpo_no' => $this->ncpo_no,
            'po_number' => $this->purchaseOrder?->po_number,
            'po_date' => $this->purchaseOrder?->transaction_date ? $this->formatReadableDateAttribute($this->purchaseOrder->transaction_date) : null,
            'project_code' => $this->purchaseOrder?->requisitionSlip?->project_department_name,
            'equipment_number' => $this->purchaseOrder?->requisitionSlip?->equipment_no,
            'justification' => $this->justification,
            'created_at' => $this->createdAtDateHuman,
        ];
    }
}
