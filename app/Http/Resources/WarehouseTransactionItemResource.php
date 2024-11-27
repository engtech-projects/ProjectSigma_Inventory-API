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
            'item_id' => $this->item_id,
            'warehouse_transaction_id' => $this->warehouse_transaction_id,
            'parent_id' => $this->parent_id,
            'quantity' => $this->quantity,
            'uom' => $this->uom,
        ];
    }
}
