<?php

namespace App\Http\Services;

use App\Models\RequestBOM;

class RequestBOMService
{
    public function getAll()
    {
        return RequestBOM::all();
    }

    public function getMyRequest()
    {
        return RequestBOM::with(['items'])
        ->where("created_by", auth()->user()->id)
        ->orderBy("created_at", "DESC")
        ->get();
    }
    public function getAllRequest()
    {
        return RequestBOM::where("request_status", "Approved")
        ->with(['itemProfiles'])
        ->orderBy("created_at", "DESC")
        ->get();
    }

    public function getMyApprovals()
    {
        $userId = auth()->user()->id;

        $result = RequestBOM::myApprovals()
                    ->with(['items'])
                    ->orderBy("created_at", "DESC")
                    ->get();

        return $result->filter(function ($item) use ($userId) {
            $nextPendingApproval = $item->getNextPendingApproval();
            return ($nextPendingApproval && $userId === $nextPendingApproval['user_id']);
        });
    }

    public function hasPendingRequest(string $assignmentType, int $assignmentId, string $effectivity): bool
    {
        return RequestBOM::where('assignment_type', $assignmentType)
            ->where('assignment_id', $assignmentId)
            ->where('effectivity', $effectivity)
            ->where('request_status', 'Pending')
            ->exists();
    }

}
