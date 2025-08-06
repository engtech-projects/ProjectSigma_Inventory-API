<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialReceivingDetailedResource extends JsonResource
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
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->supplier,
            'supplier_name' => $this->supplier?->code_name,
            'reference' => $this->reference,
            'terms_of_payment' => $this->terms_of_payment,
            'particulars' => $this->particulars,
            'transaction_date' => $this->transaction_date,
            'evaluated_by' => $this->evaluated_by,
            'items' => MaterialReceivingItemDetailedResource::collection($this->items),
            'grand_total' => $this->grand_total,
            'warehouse_name' => $this->warehouse_name,
        ];
    }
}
