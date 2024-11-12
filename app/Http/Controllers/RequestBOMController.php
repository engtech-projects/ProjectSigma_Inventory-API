<?php

namespace App\Http\Controllers;

use App\Enums\AssignTypes;
use App\Enums\RequestStatuses;
use App\Http\Requests\GetCurrentBOM;
use App\Http\Requests\GetListBOM;
use App\Models\RequestBOM;
use App\Http\Requests\StoreRequestBOMRequest;
use App\Http\Requests\UpdateRequestBOMRequest;
use App\Http\Resources\CurrentBOMResource;
use App\Http\Resources\RequestBOMResource;
use App\Http\Resources\RequestBOMResourceList;
use App\Models\Department;
use App\Models\Details;
use App\Models\Project;
use App\Notifications\RequestBOMForApprovalNotification;
use App\Traits\HasApproval;
use App\Utils\PaginateResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Services\RequestBOMService;

class RequestBOMController extends Controller
{
    use HasApproval;
    protected $requestBOMService;
    public function __construct(RequestBOMService $requestBOMService)
    {
        $this->requestBOMService = $requestBOMService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = RequestBOM::with('items')->get();
        $requestResources = RequestBOMResource::collection($requests)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);
        return response()->json([
            'message' => 'BOM Request Successfully fetched.',
            'success' => true,
            'data' => $paginated,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequestBOMRequest $request)
    {
        $attributes = $request->validated();

        $attributes['request_status'] = RequestStatuses::PENDING;
        $attributes['created_by'] = auth()->user()->id;

        if ($attributes["assignment_type"] == AssignTypes::DEPARTMENT->value) {
            $attributes["assignment_type"] = class_basename(Department::class);
        } elseif ($attributes["assignment_type"] == AssignTypes::PROJECT->value) {
            $attributes["assignment_type"] = class_basename(Project::class);
        }

        $assignmentType = $attributes["assignment_type"];
        $assignmentId = $attributes['assignment_id'];

        $existingRequest = RequestBOM::where('assignment_type', $assignmentType)
            ->where('assignment_id', $assignmentId)
            ->where('effectivity', $attributes['effectivity'])
            ->orderBy('created_at', 'desc')
            ->first();

        $version = $existingRequest ? $existingRequest->version + 1 : 1;

        if ($this->requestBOMService->hasPendingRequest($assignmentType, $assignmentId, $attributes['effectivity'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'There is already a pending Request BOM for this assignment.',
            ], JsonResponse::HTTP_CONFLICT); // 409 Conflict
        }

        DB::transaction(function () use ($attributes, $request, $version) {
            $requestBOM = RequestBOM::create([
                'assignment_id' => $attributes['assignment_id'],
                'assignment_type' => $attributes['assignment_type'],
                'effectivity' => $attributes['effectivity'],
                'approvals' => $attributes['approvals'],
                'created_by' => $attributes['created_by'],
                'request_status' => $attributes['request_status'],
                'version' => $version,
            ]);

            foreach ($attributes['details'] as $requestData) {
                $requestData['request_bom_id'] = $requestBOM->id;
                Details::create($requestData);
            }
            if ($requestBOM->getNextPendingApproval()) {
                $requestBOM->notify(new RequestBOMForApprovalNotification($request->bearerToken(), $requestBOM));
            }
        });

        return new JsonResponse([
            'success' => true,
            'message' => 'Request BOM Successfully Saved.',
        ], JsonResponse::HTTP_OK);
    }


    /**
     * Display the specified resource.
     */
    public function show(RequestBOM $resource)
    {
        $resource->load('details');
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => new RequestBOMResource($resource)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestBOMRequest $request, RequestBOM $resource)
    {
        $resource->fill($request->validated());
        if ($resource->save()) {
            return response()->json([
                "message" => "Request BOM Successfully updated.",
                "success" => true,
                "data" => $resource->refresh()
            ]);
        }
        return response()->json([
            "message" => "Failed to update.",
            "success" => false,
            "data" => $resource
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestBOM $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Request BOM not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'Request BOM successfully deleted.' : 'Failed to delete Request BOM.',
            'success' => $deleted,
            'data' => $resource
        ];

        return response()->json($response, $deleted ? 200 : 400);
    }

    public function getCurrentBom(GetCurrentBOM $request)
    {
        $validated = $request->validated();
        $assignment_type = $validated['assignment_type'] ?? null;
        $assignment_id = $validated['assignment_id'] ?? null;
        $effectivity = $validated['effectivity'] ?? null;

        $requestCurrentBom = RequestBom::with('details')
            ->where('assignment_type', $assignment_type)
            ->where('assignment_id', $assignment_id)
            ->where('effectivity', $effectivity)
            ->where('request_status', 'Approved')
            ->first();

        if (!$requestCurrentBom) {
            return response()->json([
                'message' => 'No data found.',
                'success' => false,
                'data' => []
            ]);
        }
        $requestResource = new CurrentBOMResource($requestCurrentBom);

        return response()->json([
            'message' => 'Current BOM fetched successfully.',
            'success' => true,
            'data' => $requestResource,
        ]);
    }



    public function getList(GetListBOM $request)
    {
        $validated = $request->validated();
        $assignment_type = $validated['assignment_type'] ?? null;
        $effectivity = $validated['effectivity'] ?? null;

        $filteredBomList = RequestBom::select('assignment_type', 'effectivity', 'created_by', 'request_status')
            ->where('assignment_type', $assignment_type)
            ->where('effectivity', $effectivity)
            ->get();

        if ($filteredBomList->isEmpty()) {
            return response()->json([
                'message' => 'No data found.',
                'success' => false,
                'data' => []
            ]);
        }

        return response()->json([
            'message' => 'Filtered BOM list fetched successfully.',
            'success' => true,
            'data' => $filteredBomList,
        ]);
    }

    public function myRequests()
    {
        $myRequest = $this->requestBOMService->getMyRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestBOMResourceList::collection($myRequest)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);

        return new JsonResponse([
            'success' => true,
            'message' => 'My Request Fetched.',
            'data' => $paginated
        ]);
    }
    public function allRequests()
    {
        $myRequest = $this->requestBOMService->getAll();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestBOMResourceList::collection($myRequest)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);

        return new JsonResponse([
            'success' => true,
            'message' => 'All Request Fetched.',
            'data' => $paginated
        ]);
    }

    public function myApprovals()
    {
        $myApproval = $this->requestBOMService->getMyApprovals();
        if ($myApproval->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestBOMResourceList::collection($myApproval)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);
        return new JsonResponse([
            'success' => true,
            'message' => 'My Approvals Fetched.',
            'data' => $paginated
        ]);
    }
}
