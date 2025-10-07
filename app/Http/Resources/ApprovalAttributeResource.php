<?php

namespace App\Http\Resources;

use App\Http\Services\ApiServices\HrmsService;
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
        if (is_array($this->resource) && isset($this->resource['approvals'])) {
            $approvals = $this->resource['approvals'];
            return collect($approvals)->map(function ($approval) {
                return $this->formatApproval($approval);
            })->toArray();
        }
        return $this->formatApproval($this->resource);
    }

    /**
     * Format a single approval with user information
     */
    private function formatApproval($approval): array
    {
        if (!is_array($approval)) {
            return [];
        }
        $user = User::with('employee')->find($approval['user_id'] ?? null);
        return [
            'type' => $approval['type'] ?? null,
            'status' => $approval['status'] ?? null,
            'user_id' => $approval['user_id'] ?? null,
            'remarks' => $approval['remarks'] ?? null,
            'date_approved' => $approval['date_approved'] ?? null,
            'date_denied' => $approval['date_denied'] ?? null,
            'employee_name' => $user?->employee?->fullname_first ?? $user?->name ?? 'Unknown User',
            'employee_position' => $approval['employee_position'] ?? null,
            'user_name' => $user?->name ?? 'Unknown User',
        ];
    }
}
