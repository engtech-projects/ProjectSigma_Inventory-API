<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatuses;
use App\Http\Requests\StoreBorrowTransactionRequest;
use App\Http\Resources\BorrowTransactionDetailedResource;
use App\Http\Resources\BorrowTransactionListingResource;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionItems;
use Illuminate\Http\Request;
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
        ]);
        // $transaction->notifyNextApprover(BorrowTransactionForApprovalNotification::class);
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
            'items.item'
        ]);
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => new BorrowTransactionDetailedResource($resource)
        ]);
    }
}
