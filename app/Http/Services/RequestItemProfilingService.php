<?php

namespace App\Http\Services;

use App\Models\RequestItemProfiling;

class RequestItemProfilingService
{
    public function getAll()
    {
        return RequestItemProfiling::paginate(10);
    }

    public function getMyRequest()
    {
        return RequestItemProfiling::with(['itemProfiles'])
        ->where("created_by", auth()->user()->id)
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }
    public function getAllApprovedRequest()
    {
        return RequestItemProfiling::where("request_status", "Approved")
        ->with(['itemProfiles'])
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }

    public function getMyApprovals()
    {
        $userId = auth()->user()->id;

        $result = RequestItemProfiling::myApprovals()
                    ->with(['itemProfiles'])
                    ->orderBy("created_at", "DESC")
                    ->paginate(10);

        return $result->filter(function ($item) use ($userId) {
            $nextPendingApproval = $item->getNextPendingApproval();
            return ($nextPendingApproval && $userId === $nextPendingApproval['user_id']);
        });
    }
}
