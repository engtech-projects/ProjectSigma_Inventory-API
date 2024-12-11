<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SyncItemProfilesResource extends JsonResource
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
            'item_name_summary' => $this->name_summary,
            'uom' => $this->uom,
            'uom_name' => $this->uom_full_name,
            'status' => $this->active_status
        ];
    }
}
