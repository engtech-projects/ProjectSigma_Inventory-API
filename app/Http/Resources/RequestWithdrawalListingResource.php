<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestWithdrawalListingResource extends JsonResource
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
            'date_time' => $this->date_time,
            'charging_name' => $this->chargeable_name ?? null,
            'item_codes_summary' => $this->items->pluck('item.item_code')->implode(','),
        ];
    }
}
