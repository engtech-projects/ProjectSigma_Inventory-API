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
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->warehouse->name,
            'charging_name' => $this->chargeable_name,
            'equipment_no' => $this->equipment_no,
            'created_by' => $this->createdBy->fullname_first,
            'updated_at' => $this->updated_at,
            'request_status' => $this->request_status,
        ];
    }
}
