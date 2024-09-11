<?php

namespace App\Http\Resources;

use App\Http\Services\HrmsService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalAttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return HrmsService::formatApprovals($request->bearerToken(), $this->all());
    }
}
