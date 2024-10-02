<?php

namespace App\Http\Resources;

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
        return [
            ...parent::toArray($request),
            "item_profile"  => RequestItemProfilingItemResource::collection($this->itemProfiles),
            "profile_summary" => $this->profile_summary,
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
