<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatuses;
use App\Http\Requests\StoreRequestStockRequest;
use App\Http\Resources\RequestStockResourceList;
use App\Models\RequestStock;
use App\Models\RequestStockItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Services\RequestStockService;
use App\Notifications\RequestStockForApprovalNotification;
use App\Traits\HasApproval;
use App\Http\Resources\RequestStockResource;
use App\Http\Resources\RequestStocksResource;
use App\Models\Project;

class RequestStockController extends Controller
{
    use HasApproval;
    protected $requestStockService;
    public function __construct(RequestStockService $requestStockService)
    {
        $this->requestStockService = $requestStockService;
    }

    public function index()
    {
        $main = RequestStock::with(['project'])->paginate(10);
        $collection = RequestStockResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Request Stocks Successfully Fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    public function store(StoreRequestStockRequest $request)
    {
        $attributes = $request->validated();
        $officeProject = $request->input('office_project');
        $projectCode = Project::findOrFail($officeProject)->project_code;
        $attributes['reference_no'] = 'RS' . $projectCode;
        $attributes['request_status'] = RequestStatuses::PENDING;
        $attributes['created_by'] = auth()->user()->id;

        DB::transaction(function () use ($attributes, $request) {
            $requestStock = RequestStock::create($attributes
            );

            foreach ($attributes['items'] as $item) {
                RequestStockItem::create([
                    'request_stock_id' => $requestStock->id,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'item_id' => $item['item_id'],
                    'specification' => $item['specification'],
                    'preferred_brand' => $item['preferred_brand'],
                    'reason' => $item['reason'],
                    'location' => $item['location'],
                    'location_qty' => $item['location_qty'],
                    // 'is_approved' => $item['is_approved'],
                ]);
            }

            if ($requestStock->getNextPendingApproval()) {
                $requestStock->notify(new RequestStockForApprovalNotification($request->bearerToken(), $requestStock));
            }

        });

        return new JsonResponse([
            'success' => true,
            'message' => 'Request Stock Successfull.',
        ], JsonResponse::HTTP_OK);
    }

    public function show(RequestStock $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => new RequestStocksResource($resource)
        ]);
    }


    public function destroy(RequestStock $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Request Stock not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'Request Stock successfully deleted.' : 'Failed to delete Request Stock.',
            'success' => $deleted,
            'data' => $resource
        ];

        return response()->json($response, $deleted ? 200 : 400);
    }

    public function myRequests()
    {
        $myRequest = $this->requestStockService->getMyRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        $requestResources = RequestStockResourceList::collection($myRequest)->response()->getData(true);

        return new JsonResponse([
            'message' => 'My Request Fetched.',
            'success' => true,
            'data' => $requestResources
        ]);
    }

    public function allRequests()
    {
        $myRequest = $this->requestStockService->getAllRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestStockResourceList::collection($myRequest)->response()->getData(true);

        return new JsonResponse([
            'message' => 'All Request Fetched.',
            'success' => true,
            'data' => $requestResources
        ]);
    }

    public function myApprovals()
    {
        $myApproval = $this->requestStockService->getMyApprovals();

        if ($myApproval->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestStockResourceList::collection($myApproval)->response()->getData(true);

        return new JsonResponse([
            'message' => 'My Approvals Fetched.',
            'success' => true,
            'data' => $requestResources
        ]);
    }

}
