<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseItemsResource extends JsonResource
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
            'warehouse_id' => $this->warehouse_id,
            'location' => $this->warehouse->name,
            'item_id' => $this->item_id,
            'item_name' => $this->item->name,
            'item_code' => $this->item->code,
            'item_description' => $this->item->description,
            'item_category' => $this->item->category,
            'item_group' => $this->item->group,
            'item_uom' => $this->item->uom,
            'item_quantity' => $this->quantity,
        ];
    }
}
