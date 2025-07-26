<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Services\ApiServices\HrmsService;
use App\Http\Resources\RequestProcurementDetailedResource;

class CanvasserResource extends JsonResource
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
            'id' => $this->id,
            'request_procurement_id' => $this->request_procurement_id,
            'user_id' => $this->user_id,
            'user' => HrmsService::getEmployeeDetails($request->bearerToken(), [$this->user_id]),
        ];
    }
}
