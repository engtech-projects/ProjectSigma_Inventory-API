<?php

namespace App\Http\Resources;

use App\Http\Services\ApiServices\HrmsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehousePssResource extends JsonResource
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
            'name' => $this->name,
            'warehouse_id' => $this->warehouse_id,
            'user' => HrmsService::getEmployeeDetails($request->bearerToken(), [$this->id])[0] ?? null,
        ];

    }
}
