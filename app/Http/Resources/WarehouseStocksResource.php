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
            'id'=> $this->id,
            'item_id'=> $this-> item_id,
            'item_codename' => $this->item->code_name,
            'item_code' => $this->item->item_code,
            'item_name' => $this->item->item_description,
            'item_summary' => $this->item->name_summary,
            'quantity' => $this->quantity . ' ' . $this->item->uom_full_name,
            'warehouse_transaction_id'=> $this-> warehouse_transaction_id,
            'parent_id'=> $this-> parent_id,

        ];
    }
}
