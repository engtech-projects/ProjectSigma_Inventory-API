<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialsReceivingItemResource extends JsonResource
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
            'item_code' => $this->item_profile_data['item_code'],
            'item_profile' => $this->item_profile_data['item_description'],
            'specification' => $this->item_profile_data['specification'],
            'actual_brand' => $this->actual_brand,
            'qty' => $this->qty,
            'uom' => $this->uom_name,
            'unit_price' => $this->unit_price,
            'ext_price' => $this->ext_price,
            'unit_price_formatted' => number_format($this->unit_price, 2),
            'ext_price_formatted' => number_format($this->ext_price, 2),
            'status' => $this->status,
            'remarks' => $this->remarks,
        ];
    }
}
