<?php

namespace App\Http\Resources;

use App\Http\Services\HrmsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestBOMResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            // ...parent::toArray($request),
            // 'departments' => HrmsService::getDepartments($request->bearerToken()),
            'projects' => HrmsService::getProjects($request->bearerToken()),
            // 'details' => BOMDetailsResource::collection($this->details),
            // "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            // "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
