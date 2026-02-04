<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatuses;
use App\Http\Requests\ReturnBorrowTransactionRequest;
use App\Http\Requests\StoreBorrowTransactionRequest;
use App\Http\Resources\BorrowingItemsByWarehouseResource;
use App\Http\Resources\BorrowTransactionDetailedResource;
use App\Http\Resources\BorrowTransactionListingResource;
use App\Http\Resources\BorrowTransactionResource;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionItems;
use App\Models\SetupWarehouses;
use App\Notifications\BorrowTransactionForApprovalNotification;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class BorrowTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transaction = BorrowTransaction::latest()->paginate(config('app.pagination.per_page', 10));
        return BorrowTransactionListingResource::collection($transaction)
        ->additional([
            "success" => true,
            "message" => "Borrow Transactions Successfully Fetched.",
        ]);
    }
    public function store(StoreBorrowTransactionRequest $request)
    {
        $validated = $request->validated();

        $transaction = DB::transaction(function () use ($validated) {
            $transaction = BorrowTransaction::create([
                'reference_no' => $validated['reference_no'] ?? $this->generateReferenceNumber(),
                'warehouse_id' => $validated['warehouse_id'],
                'date_time_borrowed' => $validated['date_time_borrowed'],
                'borrowed_by' => $validated['borrowed_by'],
                'borrowed_contact_no' => $validated['borrowed_contact_no'],
                'returned_by' => $validated['returned_by'] ?? null,
                'date_time_returned' => $validated['date_time_returned'] ?? null,
                'received_by' => $validated['received_by'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'approvals' => $validated['approvals'] ?? null,
                'created_by' => auth()->user()->id,
                'request_status' => RequestStatuses::PENDING,
            ]);
            foreach ($validated['items'] as $item) {
                BorrowTransactionItems::create([
                    'borrow_transaction_id' => $transaction->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
            return $transaction;
        });
        $transaction->load([
            'items.item',
            'warehouse',
        ]);
        $transaction->notifyNextApprover(BorrowTransactionForApprovalNotification::class);
        return new JsonResponse([
            'success' => true,
            'message' => 'Borrow transaction created successfully.',
            'data' => new BorrowTransactionDetailedResource($transaction),
        ], JsonResponse::HTTP_CREATED);
    }

    private function generateReferenceNumber(): string
    {
        $prefix = 'BRW';
        $date = now()->format('Ymd');
        $count = BorrowTransaction::whereDate('created_at', now()->toDateString())->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }

    public function show(BorrowTransaction $resource)
    {
        $resource->load([
            'items.item',
            'warehouse',
        ]);
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => new BorrowTransactionDetailedResource($resource)
        ]);
    }
    public function returnItems(
        ReturnBorrowTransactionRequest $request,
        BorrowTransaction $borrowTransaction
    ) {
        $validated = $request->validated();
        [$transaction, $allReturned] = DB::transaction(function () use ($borrowTransaction, $validated) {
            $allReturned = true;
            foreach ($validated['items'] as $item) {
                $borrowItem = $borrowTransaction->items()
                    ->where('id', $item['id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $metadata = $borrowItem->metadata ?? [];
                $returnedSoFar = $metadata['quantity_returned'] ?? 0;
                if ($returnedSoFar >= $borrowItem->quantity) {
                    continue;
                }
                if ($returnedSoFar + $item['quantity'] > $borrowItem->quantity) {
                    $desc = $borrowItem->item->item_description ?? "ID {$item['id']}";
                    throw new \Exception("Returned quantity exceeds borrowed quantity for item {$desc}.");
                }
                $metadata['quantity_returned'] = $returnedSoFar + $item['quantity'];
                $metadata['returned'] ??= [];
                $metadata['returned'][] = [
                    'quantity'    => $item['quantity'],
                    'returned_at' => now()->toDateTimeString(),
                    'returned_by' => $validated['returned_by'],
                    'received_by' => $validated['received_by'],
                    'remarks'     => $item['remarks'] ?? null,
                ];

                $remaining = max(0, $borrowItem->quantity - $metadata['quantity_returned']);
                if ($remaining > 0) {
                    $allReturned = false;
                }
                $borrowItem->update([
                    'metadata' => $metadata,
                    'remarks'  => $remaining === 0
                        ? 'Returned all'
                        : "Partially returned with {$remaining} remaining",
                ]);
            }
            $borrowTransaction->update([
                'date_time_returned' => $allReturned ? $validated['date_time_returned'] : null,
                'returned_by'        => $validated['returned_by'],
                'received_by'        => $validated['received_by'],
                'remarks'            => $allReturned
                    ? ($validated['remarks'] ?? 'All items returned')
                    : ($validated['remarks'] ?? 'Partially returned'),
            ]);

            return [$borrowTransaction->load('items.item'), $allReturned];
        });

        return response()->json([
            'success' => true,
            'message' => $allReturned ? 'All items returned' : 'Items partially returned',
            'data'    => new BorrowTransactionResource($transaction),
        ]);
    }

    public function getItemsByWarehouse($warehouseId)
    {
        $warehouse = SetupWarehouses::with([
            'borrowTransactions' => function ($query) {
                $query->latest('date_time_borrowed')
                    ->with(['items.item', 'borrowedBy', 'returnedBy', 'receivedBy']);
            },
        ])->findOrFail($warehouseId);

        return response()->json([
            'success' => true,
            'warehouse' => $warehouse->name,
            'items' => BorrowingItemsByWarehouseResource::collection(
                $warehouse->borrowTransactions
            ),
        ]);
    }

    public function allRequests()
    {
        $fetchData = BorrowTransaction::with('warehouse')
        ->latest()
        ->paginate(config('app.pagination.per_page', 10));
        return BorrowTransactionListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Borrow Transactions Successfully Fetched.",
        ]);
    }

    public function myApprovals()
    {
        $fetchData = BorrowTransaction::with('warehouse')
        ->latest()
        ->myApprovals()
        ->paginate(config('app.pagination.per_page', 10));
        return BorrowTransactionListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Borrow Transactions Successfully Fetched.",
        ]);
    }
    public function myRequests()
    {
        $fetchData = BorrowTransaction::with('warehouse')
        ->latest()
        ->myRequests()
        ->paginate(config('app.pagination.per_page', 10));
        return BorrowTransactionListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request Canvass Summaries Successfully Fetched.",
        ]);
    }
}
