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
            'id' => $this->id,
            'item' => [
                'id' => $this->id,
                'details' => [
                    'id' => $this->item->id,
                    'item_summary' => $this->item->item_summary,
                    'item_code' => $this->item->item_code,
                    'item_description' => $this->item->item_description,
                    'specification' => $this->item->specification,
                ],
                'warehouse_transaction_item_id' => $this->warehouse_transaction_item_id,
                'quantity' => $this->quantity,
                'uom' => $this->uom,
                'metadata' => $this->metadata,
            ],
            'warehouse_transaction_id' => $this->warehouse_transaction_id,
            'parent_id' => $this->parent_id,
        ];
    }
}
