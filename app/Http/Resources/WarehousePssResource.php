<?php

namespace App\Http\Resources;

use App\Http\Services\HrmsService;
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

        $employeeDetails = HrmsService::getEmployeeDetails($request->bearerToken(), [$this->user_id]);

        $fullNameFirst = 'Unknown';
        if (!empty($employeeDetails) && isset($employeeDetails[0]['employee']['fullname_first'])) {
            $fullNameFirst = $employeeDetails[0]['employee']['fullname_first'];
        }

        return [
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'user_id' => $this->user_id,
            'employee' => [
                'full_name' => $fullNameFirst,
            ],
        ];

    }
}
