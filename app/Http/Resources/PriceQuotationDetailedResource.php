<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceQuotationDetailedResource extends JsonResource
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
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier?->company_name,
            'supplier_address' => $this->supplier?->company_address,
            'supplier_contact_person' => $this->supplier?->contact_person_name,
            'supplier_contact_person_number' => $this->supplier?->contact_person_number,
            'reference_no' => $this->requestProcurement?->requestStock?->reference_no,
            'quotation_date' => $this->created_at_date_human,
            'items' => PriceQuotationItemDetailedResource::collection($this->items),
        ];
    }
}
