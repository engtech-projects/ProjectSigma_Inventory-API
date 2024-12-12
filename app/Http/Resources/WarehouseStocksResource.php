<?php

namespace App\Http\Resources;

use App\Models\ItemProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseStocksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'owner_id' => $this->owner_id,
            'owner_type' => $this->owner_type,
            'items' => $this->transactionItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_code' => $item->item->item_code,
                    'item_name' => $item->item->item_description,
                    'item_summary' => $item->item->name_summary,
                    'quantity' => $item->quantity . ' ' . $item->item->uom_full_name,

                ];
            }),

        ];
    }
}
