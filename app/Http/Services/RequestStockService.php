<?php

namespace App\Http\Services;

use App\Models\RequestStock;

class RequestStockService
{
    public function getAllRequest()
    {
        return RequestStock::with('project')
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }

    public function getMyRequest()
    {
        return RequestStock::with(['items', 'project', 'itemProfiles'])
        ->where("created_by", auth()->user()->id)
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }
    public function getAllApprovedRequest()
    {
        return RequestStock::where("request_status", "Approved")
        ->with(['items', 'project'])
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }

    public function getMyApprovals()
    {
        $userId = auth()->user()->id;

        $result = RequestStock::myApprovals()
                    ->with(['items', 'project'])
                    ->orderBy("created_at", "DESC")
                    ->paginate(10);

        return $result->filter(function ($item) use ($userId) {
            $nextPendingApproval = $item->getNextPendingApproval();
            return ($nextPendingApproval && $userId === $nextPendingApproval['user_id']);
        });
    }
}
