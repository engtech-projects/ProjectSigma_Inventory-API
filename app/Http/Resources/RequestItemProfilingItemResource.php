<?php

namespace App\Http\Resources;

use App\Http\Services\ItemProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestItemProfilingItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            "similar_items" => ItemProfileService::getSimilarItems($this->item_description),
        ];
    }
}
