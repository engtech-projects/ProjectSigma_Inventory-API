<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseTransactionItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'item_id'           => $this->item->id,
            'item_summary'      => $this->item->item_summary,
            'item_code'         => $this->item->item_code,
            'item_description'  => $this->item->item_description,
            'specification'     => $this->item->specification,
            'quantity'          => $this->quantity,
            'ext_price'         => $this->ext_price,
            'uom_name'          => $this->uomRelationship?->name ?? 'Unknown',
            'metadata'          => $this->metadata,
        ];
    }
}
