<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequestWithdrawalListingResource;
use App\Models\RequestWithdrawal;

class RequestWithdrawalApprovalController extends Controller
{
    /**
     * Display my approvals.
     */
    public function myApprovals()
    {
        $fetchData = RequestWithdrawal::with(['warehouse'])
            ->latest()
            ->myApprovals()
            ->paginate(config('app.pagination.per_page', 10));
        return RequestWithdrawalListingResource::collection($fetchData)
            ->additional([
                "success" => true,
                "message" => "Request Withdrawal Approvals Successfully Fetched.",
            ]);
    }
}
