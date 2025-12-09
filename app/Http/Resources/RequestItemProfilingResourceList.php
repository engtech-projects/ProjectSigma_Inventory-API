<?php

namespace App\Http\Resources;

use App\Http\Services\ItemProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestItemProfilingResourceList extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $itemProfiles = $this->itemProfiles->map(function ($itemProfile) {
            return collect($itemProfile)->only([
                'id',
                'item_description',
                'thickness',
                'length',
                'width',
                'height',
                'outside_diameter',
                'inside_diameter',
                'angle',
                'size',
                'weight',
                'volts',
                'plates',
                'part_number',
                'specification',
                'volume',
                'grade',
                'color'
            ])->put('similar_items', ItemProfileService::getSimilarItems($this->item_description))->toArray();
        })->toArray();

        return [
            "id" => $this->id,
            "profile_summary" => $this->profile_summary,
            "created_by" => $this->created_by,
            "request_status" => $this->request_status,
            "item_profile" => $itemProfiles,
            "approvals" => $this->approvals,
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
