<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatuses;
use App\Http\Requests\ReturnBorrowTransactionRequest;
use App\Http\Requests\StoreBorrowTransactionRequest;
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
                ];
                $remaining = max(0, $borrowItem->quantity - $metadata['quantity_returned']);
                $borrowItem->update([
                    'metadata' => $metadata,
                    'remarks'  => $remaining === 0
                        ? 'Returned all'
                        : "Partially returned with {$remaining} remaining",
                ]);
            }
            $borrowTransaction->load('items');
            $allReturned = $borrowTransaction->items->every(function ($item) {
                return ($item->metadata['quantity_returned'] ?? 0) >= $item->quantity;
            });
            $borrowTransaction->update([
                'date_time_returned' => $allReturned ? $validated['date_time_returned'] : null,
                'returned_by'        => $validated['returned_by'],
                'received_by'        => $validated['received_by'],
                'remarks'            => $validated['remarks']
                    ?? ($allReturned ? 'All items returned' : 'Partially returned'),
            ]);

            return [$borrowTransaction, $allReturned];
        });

        $transaction->load('items.item');

        return response()->json([
            'success' => true,
            'message' => $allReturned
                ? 'All items returned'
                : 'Items partially returned',
            'data' => new BorrowTransactionResource($transaction),
        ]);
    }
    public function getItemsByWarehouse($warehouseId)
    {
        $warehouse = SetupWarehouses::with([
            'borrowTransactions.items.item'
        ])->findOrFail($warehouseId);

        $items = $warehouse->borrowTransactions->map(function ($borrowTransaction) {
            return [
                'id' => $borrowTransaction->id,
                'reference_no' => $borrowTransaction->reference_no,
                'warehouse_id' => $borrowTransaction->warehouse_id,
                'warehouse' => $borrowTransaction->warehouse->name,
                'date_time_borrowed' => $borrowTransaction->date_time_borrowed,
                'borrowed_by' => $borrowTransaction->borrowed_by,
                'borrowed_contact_no' => $borrowTransaction->borrowed_contact_no,
                'returned_by' => $borrowTransaction->returned_by,
                'date_time_returned' => $borrowTransaction->date_time_returned,
                'received_by' => $borrowTransaction->received_by,
                'remarks' => $borrowTransaction->remarks,
                'items' => $borrowTransaction->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'borrow_transaction_id' => $item->borrow_transaction_id,
                        'item_id' => $item->item_id,
                        'item_description' => $item->item->item_description ?? null,
                        'current_quantity' => $item->quantity,
                        'quantity_returned' => $item->metadata['quantity_returned'] ?? 0,
                        'remaining_quantity' => $item->quantity - ($item->metadata['quantity_returned'] ?? 0),
                        'remarks' => $item->remarks,
                        'metadata' => $item->metadata,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'warehouse' => $warehouse->name,
            'items' => $items,
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
