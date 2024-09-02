<?php

namespace App\Http\Services;

use App\Models\ItemProfile;
use App\Models\RequestItemProfiling;

class ItemProfileService
{
    protected $itemProfileRequest;

    public function __construct(ItemProfile $itemProfileRequest)
    {
        $this->itemProfileRequest = $itemProfileRequest;
    }

    public function getAll()
    {
        return ItemProfile::all();
    }

    public function getMyRequest()
    {
        return RequestItemProfiling::with(['itemProfiles'])
        ->where("created_by", auth()->user()->id)
        ->get();
    }
    public function getAllRequest()
    {
        return RequestItemProfiling::with(['itemProfiles'])
        ->get();
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
;            return ($nextPendingApproval && $userId === $nextPendingApproval['user_id']);
        });
    }
}
