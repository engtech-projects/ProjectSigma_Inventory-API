<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatuses;
use App\Http\Requests\StoreRequestStockRequest;
use App\Models\RequestStock;
use App\Models\RequestStockItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestStockController extends Controller
{
    public function store(StoreRequestStockRequest $request, $warehouse_id)
{
    $attributes = $request->validated();

    $attributes['request_no'] = 'RS-' . date('Ymd') . '-' . strtoupper(str_pad((string) RequestStock::whereDate('created_at', date('Y-m-d'))->count() + 1, 4, '0', STR_PAD_LEFT));
    $attributes['request_status'] = RequestStatuses::PENDING;
    $attributes['created_by'] = auth()->user()->id;
    $attributes['warehouse_id'] = $warehouse_id;

    DB::transaction(function () use ($attributes) {

        $requestStock = RequestStock::create($attributes
        );

        foreach ($attributes['items'] as $item) {
            RequestStockItem::create([
                'request_stock_id' => $requestStock->id,
                'item_id' => $item['item_id'],
                'qty' => $item['qty'],
                'uom' => $item['uom'],
                'item_description' => $item['item_description'],
                'specification' => $item['specification'],
                'preferred_brand' => $item['preferred_brand'],
                'reason' => $item['reason'],
                'location' => $item['location'],
                'is_approved' => $item['is_approved'],
                'type_of_request' => $item['type_of_request'],
                'contact_no' => $item['contact_no'],
                'remarks' => $item['remarks'],
                'current_smr' => $item['current_smr'],
                'previous_smr' => $item['previous_smr'],
                'unused_smr' => $item['unused_smr'],
                'next_smr' => $item['next_smr'],
            ]);
        }

        // if ($requestStock->approvals) {
        //     // Notify users about the stock request if required
        //     // Example: NotifyApprovalService::notify($requestStock);
        // }
    });

    return new JsonResponse([
        'success' => true,
        'message' => 'Request Stock Successfull.',
    ], JsonResponse::HTTP_OK);
}

}
