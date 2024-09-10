<?php

namespace App\Http\Resources;

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
        $user = User::with('employee')->where('id', $this["user_id"])->first();
        $employee = null;
        if ($user) {
            $employee = $user->employee ? new EmployeeUserResource($user->employee) : null;
        }
        return [
            "type" => $this["type"],
            "status" => $this["status"] ?? null,
            "userselector" => array_key_exists("userselector", $this->resource) ? $this['userselector'] : null,
            "user_id" => $this["user_id"] ?? null,
            "remarks" => $this["remarks"] ?? null,
            "date_approved" => $this["date_approved"] ?? null,
            "date_denied" => $this["date_denied"] ?? null,
            "employee" => $employee
        ];
    }
}
