<?php

namespace App\Http\Services;

use App\Models\RequestSupplier;
use App\Traits\Filters;

class RequestSupplierService
{
    use Filters;

    public function getAll()
    {
        return RequestSupplier::all();
    }

    public function getMyRequest()
    {
        return RequestSupplier::with(['uploads'])
        ->where("created_by", auth()->user()->id)
        ->orderBy('created_at', 'DESC')->get();
    }

    public function getAllRequest()
    {
        return RequestSupplier::where("request_status", "Approved")
        ->with(['uploads'])
        ->orderBy("created_at", "DESC")
        ->get();
    }
    public function getAllApprovedRequest()
    {
        return RequestSupplier::where("request_status", "Approved")
        ->with(['uploads'])
        ->orderBy("created_at", "DESC")
        ->get();
    }

    public function getMyApprovals()
    {
        $userId = auth()->user()->id;

        $result = RequestSupplier::myApprovals()
            ->with(['uploads'])
            ->orderBy("created_at", "DESC")
            ->get();

        return $result->filter(function ($item) use ($userId) {
            $nextPendingApproval = $item->getNextPendingApproval();

            return ($nextPendingApproval && $userId === (int)$nextPendingApproval['user_id']);
        });
    }

}
