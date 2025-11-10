<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsolidatedRequestDetailedResource extends JsonResource
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
            'purpose' => $this->purpose,
            'consolidated_by' => $this->consolidated_by,
            'date_consolidated' => $this->date_consolidated,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'metadata' => $this->metadata,
            'items' => $this->items->load(['requisitionSlipItem.itemProfile' => function ($query) {
                $query->select('id', 'item_description', 'item_code');
            }])->map(function ($item) {
                return [
                    'id' => $item->requisitionSlipItem->itemProfile->id,
                    'item_description' => $item->requisitionSlipItem->itemProfile->item_description,
                    'quantity' => $item->quantity_consolidated,
                    'uom_name' => $item->requisitionSlipItem->uom_name,
                    'remarks' => $item->remarks,
                    'status' => $item->status,
                ];
            }),
        ];
    }
}
