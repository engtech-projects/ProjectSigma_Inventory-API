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
        return HrmsService::formatApprovals($request->bearerToken(), $this->resource);
        // if (is_array($this->resource) && isset($this->resource['approvals'])) {
        //     $approvals = $this->resource['approvals'];
        //     return collect($approvals)->map(function ($approval) {
        //         return $this->formatApproval($approval);
        //     })->toArray();
        // }
        // return $this->formatApproval($this->resource);
    }

    /**
     * Format a single approval with user information
     */
    // private function formatApproval($approval): array
    // {
    //     if (!is_array($approval)) {
    //         return [];
    //     }
    //     $user = User::with('employee')->find($approval['user_id'] ?? null);
    //     $dateApproved = $approval['date_approved'] ?? null;
    //     $dateDenied = $approval['date_denied'] ?? null;

    //     return [
    //         'type' => $approval['type'] ?? null,
    //         'user_id' => $approval['user_id'] ?? null,
    //         'user_name' => $user?->employee?->fullname_first ?? $user?->name ?? 'Unknown User',
    //         'position' => $user?->employee?->position ?? 'N/A',
    //         'status' => $approval['status'] ?? null,
    //         'date_approved' => $dateApproved ? date('M d, Y h:i A', strtotime($dateApproved)) : null,
    //         'date_denied' => $dateDenied ? date('M d, Y h:i A', strtotime($dateDenied)) : null,
    //         'approval_timestamp' => $dateApproved ?? $dateDenied,
    //         'remarks' => $approval['remarks'] ?? null,
    //     ];
    // }
}
