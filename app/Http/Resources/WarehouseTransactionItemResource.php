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
        $uomName = $this->uom instanceof UOM
            ? $this->uom->name
            : optional(UOM::find($this->uom))->name;
        return [
            'id'                => $this->id,
            'item_id'           => $this->item->id,
            'item_summary'      => $this->item->item_summary,
            'item_code'         => $this->item->item_code,
            'item_description'  => $this->item->item_description,
            'specification'     => $this->item->specification,
            'quantity'          => $this->quantity,
            'ext_price'         => $this->ext_price,
            'uom'               => $uomName ?? 'Unknown',
            'metadata'          => $this->metadata,
        ];
    }
}
