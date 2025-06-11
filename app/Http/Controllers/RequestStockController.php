<?php

namespace App\Http\Controllers;

use App\Enums\AssignTypes;
use App\Enums\RequestStatuses;
use App\Http\Requests\StoreRequestStockRequest;
use App\Http\Resources\RequestStockResourceList;
use App\Models\RequestStock;
use App\Models\RequestStockItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Services\RequestStockService;
use App\Notifications\RequestStockForApprovalNotification;
use App\Traits\HasApproval;
use App\Http\Resources\RequestStocksResource;
use App\Models\Department;
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
        $main = RequestStock::with(['project', 'items', 'currentBom'])->paginate(10);
        $collection = RequestStocksResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Request Stocks Successfully Fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    public function store(StoreRequestStockRequest $request)
    {
        $attributes = $request->validated();
        $sectionId = $attributes['section_id'];

        if ($attributes["section_type"] == AssignTypes::DEPARTMENT->value) {
            $attributes["section_type"] = class_basename(Department::class);
        } elseif ($attributes["section_type"] == AssignTypes::PROJECT->value) {
            $attributes["section_type"] = class_basename(Project::class);
        }

        $attributes['request_status'] = RequestStatuses::PENDING;
        $attributes['created_by'] = auth()->user()->id;

        // Generate reference number with retry logic
        if ($attributes["section_type"] == class_basename(Department::class)) {
            $this->generateDepartmentReferenceNumber($attributes, $sectionId);
        } else {
            $this->generateProjectReferenceNumber($attributes, $sectionId);
        }

        // Execute transaction with retry logic
        $maxRetries = 5;
        $attempt = 0;

        do {
            try {
                return DB::transaction(function () use ($attributes, $request) {
                    $duplicatedAttr = RequestStock::where('reference_no', $attributes['reference_no'])
                        ->orWhere('equipment_no', $attributes['equipment_no'])
                        ->first();

                    if ($duplicatedAttr) {
                        throw new \Exception(
                            $duplicatedAttr->reference_no == $attributes['reference_no']
                                ? 'The reference number has already been taken.'
                                : 'The equipment number has already been taken.'
                        );
                    }

                    $requestStock = RequestStock::create($attributes);

                    foreach ($attributes['items'] as $item) {
                        RequestStockItem::create([
                            'request_stock_id' => $requestStock->id,
                            'quantity' => $item['quantity'],
                            'unit' => $item['unit'],
                            'item_id' => $item['item_id'],
                            'specification' => $item['specification'],
                            'preferred_brand' => $item['preferred_brand'],
                            'reason' => $item['reason'],
                        ]);
                    }

                    if ($requestStock->getNextPendingApproval()) {
                        $requestStock->notify(new RequestStockForApprovalNotification($request->bearerToken(), $requestStock));
                    }

                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Requisition Slip Successfully Submitted.',
                    ], JsonResponse::HTTP_OK);
                });

            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry error
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Unable to generate unique reference number after ' . $maxRetries . ' attempts',
                        ], JsonResponse::HTTP_CONFLICT);
                    }

                    // Regenerate reference number for department type
                    if ($attributes["section_type"] == class_basename(Department::class)) {
                        $this->generateDepartmentReferenceNumber($attributes, $sectionId);
                    } else if ($attributes["section_type"] == class_basename(Project::class)) {
                        $this->generateProjectReferenceNumber($attributes, $sectionId);
                    }

                    // Short delay before retry
                    usleep(100000); // 100ms
                } else {
                    throw $e; // Re-throw if it's not a duplicate key error
                }
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], JsonResponse::HTTP_CONFLICT);
            }
        } while ($attempt < $maxRetries);
    }

    private function generateDepartmentReferenceNumber(array &$attributes, int $sectionId): void
    {
        $departmentCode = strtoupper(implode('-', array_map('ucwords', explode(' ', Department::findOrFail($sectionId)->department_name))));

        $baseRef = "RS{$departmentCode}";
        $increment = RequestStock::where('reference_no', 'regexp', "^{$baseRef}-[0-9]+$")->count() + 1;
        $attributes['reference_no'] = $baseRef . '-' . str_pad($increment, 7, '0', STR_PAD_LEFT);
    }

    private function generateProjectReferenceNumber(array &$attributes, int $sectionId): void
    {
        $projectCode = Project::findOrFail($sectionId)->project_code;
        $latest    = RequestStock::where('reference_no', 'regexp', "^RS{$projectCode}-[0-9]$")
                        ->orderBy('reference_no', 'desc')
                        ->lockForUpdate()
                        ->value('reference_no');
        $next      = $latest ? ((int)substr($latest, strlen("RS{$projectCode}-")) + 1) : 1;
        $attributes['reference_no'] = "RS{$projectCode}-" . str_pad($next, 7, '0', STR_PAD_LEFT);
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
