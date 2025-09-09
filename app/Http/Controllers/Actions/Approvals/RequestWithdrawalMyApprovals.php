<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Http\Resources\RequestWithdrawalListingResource;
use App\Http\Controllers\Controller;
use App\Models\RequestWithdrawal;

class RequestWithdrawalMyApprovals extends Controller
{
    /**
     * Display my approvals.
     */
    public function __invoke()
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
