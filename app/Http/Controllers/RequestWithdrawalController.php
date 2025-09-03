<?php

namespace App\Http\Controllers;

use App\Models\RequestWithdrawal;
use App\Http\Requests\StoreRequestWithdrawalRequest;
use App\Http\Resources\RequestWithdrawalListingResource;
use App\Http\Resources\RequestWithdrawalDetailedResource;
use App\Models\RequestWithdrawalItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\OwnerType;
use Throwable;

class RequestWithdrawalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $withdrawals = RequestWithdrawal::with(['items.item', 'warehouse'])
                ->latest()
                ->paginate(config('app.pagination.per_page', 10));

            if ($withdrawals->isEmpty()) {
                return response()->json([
                    'message' => 'No Request Withdrawals found.',
                    'success' => false,
                    'data'    => [],
                ], 200);
            }

            return RequestWithdrawalListingResource::collection($withdrawals)
                ->additional([
                    'message' => 'Request Withdrawals retrieved successfully.',
                    'success' => true,
                ]);
        } catch (Throwable $e) {
            Log::error('Error fetching withdrawals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve Request Withdrawals.',
                'success' => false,
                'error'   => config('app.debug') ? $e->getMessage() : 'Server Error',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequestWithdrawalRequest $request)
    {
        try {
            $data = $request->validated();

            $withdrawal = DB::transaction(function () use ($data) {
                $withdrawal = RequestWithdrawal::create([
                    'date_time'       => $data['date_time'],
                    'warehouse_id'    => $data['warehouse_id'],
                    'chargeable_id'   => $data['chargeable_id'],
                    'chargeable_type' => OwnerType::from($data['chargeable_type'])->value,
                    'equipment_no'    => $data['equipment_no'] ?? null,
                    'smr'             => $data['smr'] ?? null,
                    'fuel'            => $data['fuel'],
                    'reference_no'    => $data['reference_no'] ?? null,
                    'metadata'        => $data['metadata'] ?? null,
                    'approvals'       => $data['approvals'] ?? [],
                    'created_by'      => auth()->user()->id,
                ]);

                $items = collect($data['items'])->map(function ($item) use ($withdrawal) {
                    return [
                        'request_withdrawal_id' => $withdrawal->id,
                        'item_id'               => $item['item_id'],
                        'quantity'              => $item['quantity'],
                        'uom_id'                => $item['uom_id'],
                        'purpose_of_withdrawal' => $item['purpose_of_withdrawal'] ?? null,
                        'metadata'              => $item['metadata'] ?? null,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                });

                if ($items->isNotEmpty()) {
                    RequestWithdrawalItem::insert($items->toArray());
                }

                return $withdrawal->fresh(['warehouse', 'chargeable', 'items.item', 'items.uom']);
            });

            return (new RequestWithdrawalDetailedResource($withdrawal))
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
        try {
            $resource->load([
                'warehouse',
                'chargeable',
                'items.item',
                'items.uom',
            ]);

            return (new RequestWithdrawalDetailedResource($resource))
                ->additional([
                    'message' => 'Request Withdrawal retrieved successfully.',
                    'success' => true,
                ]);
        } catch (Throwable $e) {
            Log::error('Error fetching withdrawal details', [
                'id'    => $resource->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve Request Withdrawal.',
                'success' => false,
                'error'   => config('app.debug') ? $e->getMessage() : 'Server Error',
            ], 500);
        }
    }
}
