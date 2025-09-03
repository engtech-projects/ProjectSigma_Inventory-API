<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestWithdrawalItemDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'item_code'            => $this->whenLoaded('item', fn () => $this->item->code),
            'item_name_summary'    => $this->whenLoaded('item', fn () => $this->item->name_summary),
            'unit_name'            => $this->whenLoaded('uom', fn () => $this->uom->name),
            'quantity'             => $this->quantity,
            'purpose_of_withdrawal' => $this->purpose_of_withdrawal,
        ];
    }
}
