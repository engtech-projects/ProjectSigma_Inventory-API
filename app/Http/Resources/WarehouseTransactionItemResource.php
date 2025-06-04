<?php

namespace App\Http\Resources;

use App\Models\UOM;
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
        $uomName = UOM::find($this->uom)->name ?? $this->uom;
        return [
            'id' => $this->id,
            'item_id' => $this->item->id,
            'item_summary' => $this->item->item_summary,
            'item_code' => $this->item->item_code,
            'item_description' => $this->item->item_description,
            'specification' => $this->item->specification,
            'warehouse_transaction_d' => $this->warehouse_transaction_id,
            'quantity' => $this->quantity,
            'accepted_quantity' => $this->quantity,
            'ext_price' => $this->ext_price,
            'uom' => $uomName,
            'metadata' => $this->metadata,
            'warehouse_transaction_id' => $this->warehouse_transaction_id,
            'parent_id' => $this->parent_id,
        ];
    }
}

