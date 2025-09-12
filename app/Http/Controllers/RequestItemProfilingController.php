<?php

namespace App\Http\Controllers;

use App\Enums\ItemProfileActiveStatus;
use App\Enums\RequestStatuses;
use App\Http\Requests\StoreRequestItemProfilingRequest;
use App\Models\RequestItemProfiling;
use App\Http\Requests\UpdateRequestItemProfilingRequest;
use App\Http\Resources\ItemProfileResource;
use App\Http\Resources\RequestItemProfilingResource;
use App\Http\Resources\RequestItemProfilingResourceList;
use App\Http\Services\RequestItemProfilingService;
use App\Models\ItemProfile;
use App\Models\RequestItemProfilingItems;
use App\Notifications\RequestItemProfilingForApprovalNotification;
use App\Traits\HasApproval;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RequestItemProfilingController extends Controller
{
    use HasApproval;

    protected $requestItemProfilingService;
    public function __construct(RequestItemProfilingService $requestItemProfilingService)
    {
        $this->requestItemProfilingService = $requestItemProfilingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = RequestItemProfiling::with('itemProfiles')->paginate(10);
        $collection = RequestItemProfilingResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Request Item Profiling Successfully Fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    public function get()
    {
        $main = ItemProfile::IsApproved()->paginate(10);
        $collection = ItemProfileResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreRequestItemProfilingRequest $request)
    {
        $attributes = $request->validated();
        $attributes['request_status'] = RequestStatuses::PENDING;
        $attributes['created_by'] = auth()->user()->id;
        $requestItemProfiling = DB::transaction(function () use ($attributes, $request) {
            $requestItemProfiling = RequestItemProfiling::create([
                'approvals' => $attributes['approvals'],
                'created_by' => $attributes['created_by'],
                'request_status' => $attributes['request_status'],
            ]);
            foreach ($attributes['item_profiles'] as $itemprofileData) {
                $itemprofileData['request_itemprofiling_id'] = $requestItemProfiling->id;
                $itemprofileData['active_status'] = ItemProfileActiveStatus::ACTIVE;

                $itemProfile = ItemProfile::create($itemprofileData);

                RequestItemProfilingItems::create([
                    'item_profile_id' => $itemProfile->id,
                    'request_itemprofiling_id' => $requestItemProfiling->id,
                ]);
            }
            return $requestItemProfiling->refresh();
        });
        $requestItemProfiling->notifyNextApprover(RequestItemProfilingForApprovalNotification::class);
        return new JsonResponse([
            'success' => true,
            'message' => 'Item Profiles Successfully Saved.',
            // 'data' => $attributes['item_profiles'],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestItemProfiling $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => new RequestItemProfilingResource($resource)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestItemProfilingRequest $request, RequestItemProfiling $resource)
    {
        $resource->fill($request->validated());
        if ($resource->save()) {
            return response()->json([
                "message" => "Successfully updated.",
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
    public function destroy(RequestItemProfiling $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Item Profile not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'Item Profile successfully deleted.' : 'Failed to delete Item Profile.',
            'success' => $deleted,
            'data' => $resource
        ];

        return response()->json($response, $deleted ? 200 : 400);
    }

    public function myRequests()
    {
        $myRequest = $this->requestItemProfilingService->getMyRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestItemProfilingResourceList::collection($myRequest)->response()->getData(true);

        return new JsonResponse([
            'success' => true,
            'message' => 'My Request Fetched.asdf',
            'data' => $requestResources
        ]);
    }
    public function allRequests()
    {
        $myRequest = $this->requestItemProfilingService->getAll();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestItemProfilingResourceList::collection($myRequest)->response()->getData(true);

        return new JsonResponse([
            'success' => true,
            'message' => 'All Request Fetched.',
            'data' => $requestResources
        ]);
    }
    public function allApprovedRequests()
    {
        $myRequest = $this->requestItemProfilingService->getAllApprovedRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestItemProfilingResourceList::collection($myRequest)->response()->getData(true);

        return new JsonResponse([
            'success' => true,
            'message' => 'All Approved Requests Fetched.',
            'data' => $requestResources
        ]);
    }

    public function myApprovals()
    {
        $myApproval = $this->requestItemProfilingService->getMyApprovals();

        if ($myApproval->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestItemProfilingResourceList::collection($myApproval)->response()->getData(true);

        return new JsonResponse([
            'message' => 'My Approvals Fetched.asdf',
            'success' => true,
            'data' => $requestResources
        ]);
    }
}
