<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatuses;
use App\Models\RequestWithdrawal;
use App\Http\Requests\StoreRequestWithdrawalRequest;
use App\Http\Resources\RequestWithdrawalListingResource;
use App\Http\Resources\RequestWithdrawalDetailedResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RequestWithdrawalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $withdrawals = RequestWithdrawal::with(['items.item', 'warehouse'])
            ->latest()
            ->paginate(config('app.pagination.per_page', 10));
        return RequestWithdrawalListingResource::collection($withdrawals)
            ->additional([
                'message' => $withdrawals->isEmpty() ? 'No Request Withdrawals found.' : 'Request Withdrawals retrieved successfully.',
                'success' => true,
            ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequestWithdrawalRequest $request)
    {
        try {
            $data = $request->validated();
            $withdrawal = DB::transaction(function () use ($data) {
                $data['reference_no'] = $this->generateReferenceNo();
                $data['created_by'] = auth()->user()->id;
                $data['request_status'] = RequestStatuses::PENDING->value;
                $withdrawal = RequestWithdrawal::create($data);
                $withdrawal->items()->createMany($data['items']);
                return $withdrawal->fresh(['warehouse', 'chargeable', 'items.item', 'items.uom']);
            });
            return (RequestWithdrawalDetailedResource::make($withdrawal))
                ->additional([
                    'message' => 'Request Withdrawal created successfully.',
                    'success' => true,
                ]);
        } catch (Throwable $e) {
            Log::error('Error storing withdrawal', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to create Request Withdrawal.',
                'success' => false,
                'error'   => config('app.debug') ? $e->getMessage() : 'Server Error',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestWithdrawal $resource)
    {
        $resource->load([
            'warehouse',
            'chargeable',
            'items.item',
            'items.uom',
        ]);
        return (RequestWithdrawalDetailedResource::make($resource))
            ->additional([
                'message' => $resource ? 'Request Withdrawal retrieved successfully.' : 'Request Withdrawal not found.',
                'success' => true,
            ]);
    }

    private function generateReferenceNo(): string
    {
        $last = RequestWithdrawal::latest('id')->first();
        $nextId = $last ? $last->id + 1 : 1;

        return sprintf("RW-%s-%05d", now()->year, $nextId);
    }
}
