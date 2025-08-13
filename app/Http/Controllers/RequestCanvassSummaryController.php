<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatuses;
use App\Http\Requests\StoreCanvassSummary;
use App\Http\Resources\RequestCanvassSummaryListingResource;
use App\Http\Resources\RequestCanvassSummaryResource;
use App\Models\PriceQuotationItem;
use App\Models\RequestCanvassSummary;
use App\Models\RequestCanvassSummaryItems;
use App\Notifications\RequestCanvassSummaryForApprovalNotification;
use App\Traits\HasApproval;
use App\Traits\HasCSNumber;
use App\Traits\ModelHelpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RequestCanvassSummaryController extends Controller
{
    use HasApproval;
    use ModelHelpers;
    use HasCSNumber;

    public function index()
    {
        $requestCanvassSummaries = RequestCanvassSummary::latest()->paginate(config('app.pagination.per_page', 10));
        return RequestCanvassSummaryListingResource::collection($requestCanvassSummaries)
        ->additional([
            "success" => true,
            "message" => "Request Canvass Summaries Successfully Fetched.",
        ]);
    }

    public function store(StoreCanvassSummary $request)
    {
        $validated = $request->validated();
        $summary = DB::transaction(function () use ($validated) {
            $summary = RequestCanvassSummary::create([
                'price_quotation_id' => $validated['price_quotation_id'],
                'terms_of_payment' => $validated['terms_of_payment'],
                'availability' => $validated['availability'],
                'delivery_terms' => $validated['delivery_terms'],
                'remarks' => $validated['remarks'],
                'cs_number' => $this->generateCsNumber(),
                'approvals' => $validated['approvals'],
                'created_by' => auth()->user()->id,
                'request_status' => RequestStatuses::PENDING,
            ]);
            $quotationItems = PriceQuotationItem::where('price_quotation_id', $validated['price_quotation_id'])
                ->pluck('unit_price', 'item_id');
            foreach ($validated['items'] as $item) {
                RequestCanvassSummaryItems::create([
                    'request_canvass_summary_id' => $summary->id,
                    'item_id' => $item['item_id'],
                    'unit_price' => $quotationItems[$item['item_id']] ?? 0,
                ]);
            }
            return $summary;
        });
        $summary->load([
            'priceQuotation',
            'items.itemProfile'
        ]);
        if ($summary->getNextPendingApproval()) {
            $summary->notify(new RequestCanvassSummaryForApprovalNotification($request->bearerToken(), $summary));
        }
        return new JsonResponse([
            'success' => true,
            'message' => 'Canvass summary created successfully.',
            'data' => new RequestCanvassSummaryResource($summary),
        ], JsonResponse::HTTP_CREATED);
    }
    public function show(RequestCanvassSummary $requestCanvassSummary)
    {
        $requestCanvassSummary->load([
            'priceQuotation',
            'items.itemProfile',
        ]);
        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => new RequestCanvassSummaryListingResource($requestCanvassSummary)
        ]);
    }

    public function myRequests()
    {
        $fetchData = RequestCanvassSummary::latest()
        ->myRequests()
        ->paginate(config('app.pagination.per_page', 10));
        return RequestCanvassSummaryListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request Canvass Summaries Successfully Fetched.",
        ]);
    }

    public function myApprovals()
    {
        $fetchData = RequestCanvassSummary::latest()
        ->myApprovals()
        ->paginate(config('app.pagination.per_page', 10));
        return RequestCanvassSummaryListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request Canvass Summaries Successfully Fetched.",
        ]);
    }
}
