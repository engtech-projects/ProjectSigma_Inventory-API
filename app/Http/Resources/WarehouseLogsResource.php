<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseLogsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
        [
            'transaction_date' => $this->created_at_date_human,
            'transaction_type' => $this->type,
            'item_code' => $this->item->item_code,
            'movement' => $this->movement,
        ];
    }
}
