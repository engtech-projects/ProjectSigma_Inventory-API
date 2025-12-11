<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestWithdrawalDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'date_time'      => $this->date_time,
            'warehouse_name' => $this->warehouse?->name,
            'charging_name'  => $this->chargeable_name ?? null,
            'requested_by'   => $this->created_by_user_name ?? null,
            'equipment_no'   => $this->equipment_no,
            'smr'            => $this->smr,
            'fuel'           => $this->fuel,
            'items'          => $this->whenLoaded(
                'items',
                fn () => RequestWithdrawalItemDetailedResource::collection($this->items),
                []
            ),
            'approvals' => ApprovalAttributeResource::collection($this->approvals)
        ];
    }
}
