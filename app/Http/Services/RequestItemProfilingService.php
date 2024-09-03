<?php

namespace App\Http\Services;
use App\Models\RequestItemProfiling;

class RequestItemProfilingService
{
    protected $requestItemProfilingRequest;

    public function __construct(RequestItemProfiling $requestItemProfilingRequest)
    {
        $this->requestItemProfilingRequest = $requestItemProfilingRequest;
    }

    public function getAll()
    {
        return RequestItemProfiling::all();
    }

    public function getMyRequest()
    {
        return RequestItemProfiling::with(['itemProfiles'])
        ->where("created_by", auth()->user()->id)
        ->get();
    }
    public function getAllRequest()
    {
        return RequestItemProfiling::where("request_status", "Approved")->with(['itemProfiles'])->get();
        // return RequestItemProfiling::with(['itemProfiles'])->get();
    }

    public function getMyApprovals()
    {
        $userId = auth()->user()->id;
        $result = RequestItemProfiling::with(['itemProfiles'])
            ->requestStatusPending()
            ->authUserPending()
            ->get();

        return $result->filter(function ($item) use ($userId) {
            $nextPendingApproval = $item->getNextPendingApproval();
            ;
            return ($nextPendingApproval && $userId === $nextPendingApproval['user_id']);
        });
    }
}
