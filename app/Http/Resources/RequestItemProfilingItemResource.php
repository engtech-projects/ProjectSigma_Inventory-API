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

    // Specific similar items list
    // private function selectedSimilarItems(): array
    // {
    //     $similarItems = ItemProfileService::getSimilarItems($this->item_description);

    //     return $similarItems->map(function ($item) {
    //         return [
    //             'sku' => $item->sku,
    //             'item_description' => $item->item_description,
    //         ];
    //     })->toArray();
    // }
}
