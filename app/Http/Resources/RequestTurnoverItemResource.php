<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestTurnoverItemResource extends JsonResource
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
            'request_turnover_id' => $this->request_turnover_id,
            'item_id' => $this->item_id,
            'item_description' => $this->item->item_description ?? null,
            'quantity' => $this->quantity,
            'uom' => $this->uom,
            'uom_name' => $this->uom_name,
            'condition' => $this->condition,
            'remarks' => $this->remarks,
            'accept_status' => $this->accept_status,
            'status_flags' => [
                'is_pending' => $this->isPending(),
                'is_accepted' => $this->isAccepted(),
                'is_denied' => $this->isDenied(),
                'can_be_accepted' => $this->canBeAccepted(),
                'can_be_denied' => $this->canBeDenied(),
            ],
        ];
    }
}
