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
            'item_code'            => $this->item->code ?? null,
            'item_name_summary'    => $this->item->name_summary ?? null,
            'unit_name'            => $this->uom->name ?? null,
            'quantity'             => $this->quantity,
            'purpose_of_withdrawal' => $this->purpose_of_withdrawal,
        ];
    }
}
