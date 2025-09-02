<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequestWithdrawalListingResource;
use App\Models\RequestWithdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class RequestWithdrawalApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $userId = auth()->id();

            $approvals = RequestWithdrawal::isApproved()
                ->latest()
                ->paginate(config('app.pagination.per_page', 10));

            // Handle not found / empty collection
            if ($approvals->isEmpty()) {
                return response()->json([
                    'message' => 'No Request Withdrawal Approvals found.',
                    'success' => false,
                    'data'    => [],
                ], 404);
            }

            return RequestWithdrawalListingResource::collection($approvals)
                ->additional([
                    'message' => 'Request Withdrawal Approvals retrieved successfully.',
                    'success' => true,
                ])
                ->response()
                ->setStatusCode(200);
        } catch (Throwable $e) {
            Log::error('Error fetching Request Withdrawal Approvals', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve Request Withdrawal Approvals.',
                'success' => false,
                'error'   => config('app.debug') ? $e->getMessage() : 'Server Error',
            ], 500);
        }
    }
}
