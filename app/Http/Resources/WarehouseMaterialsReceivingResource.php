<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseMaterialsReceivingResource extends JsonResource
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
            // ...parent::toArray($request),
            "id"=> $this->id,
            "name" => $this->name,
            "location" => $this->location,
            "owner_type" =>$this->owner_type,
            'materials_receiving' => MaterialsReceivingResource::collection($this->materialsReceiving->load('items')),
        ];
    }
}
