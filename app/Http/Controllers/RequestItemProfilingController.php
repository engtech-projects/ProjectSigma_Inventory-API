<?php

namespace App\Http\Controllers;

use App\Enums\ItemProfileActiveStatus;
use App\Enums\RequestApprovalStatus;
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
use App\Utils\PaginateResourceCollection;
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
        $requests = RequestItemProfiling::with('itemProfiles')->paginate(10);
        $data = json_decode('{}');
        $data->message = "Request Item Profiling Successfully Fetched.";
        $data->success = true;
        $data->data = $requests;
        return response()->json($data);
    }

    public function get()
    {
        $main = ItemProfile::IsApproved()->get();
        $requestResources = ItemProfileResource::collection($main)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);
        return response()->json([
            'message' => 'Successfully fetched.',
            'success' => true,
            'data' => $paginated,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreRequestItemProfilingRequest $request)
    {
        $attributes = $request->validated();
        $attributes['request_status'] = RequestApprovalStatus::PENDING;
        $attributes['created_by'] = auth()->user()->id;

        // try {
        DB::transaction(function () use ($attributes, $request) {
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
            if ($requestItemProfiling->getNextPendingApproval()) {
                $requestItemProfiling->notify(new RequestItemProfilingForApprovalNotification($request->bearerToken(), $requestItemProfiling));
            }
        });
        return new JsonResponse([
            'success' => true,
            'message' => 'Item Profiles Successfully Saved.',
            // 'data' => $attributes['item_profiles'],
        ], JsonResponse::HTTP_OK);

        // } catch (\Exception $e) {
        //     return response()->json([
        //         'message' => 'Failed to save Item Profiles.',
        //         'error' => $e->getMessage(),
        //     ], 400);
        // }
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

        $requestResources = RequestItemProfilingResourceList::collection($myRequest)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);

        return new JsonResponse([
            'success' => true,
            'message' => 'My Request Fetched.',
            'data' => $paginated
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

        $requestResources = RequestItemProfilingResourceList::collection($myRequest)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);

        return new JsonResponse([
            'success' => true,
            'message' => 'All Request Fetched.',
            'data' => $paginated
        ]);
    }
    public function allApprovedRequests()
    {
        $myRequest = $this->requestItemProfilingService->getAllRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestItemProfilingResourceList::collection($myRequest)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);

        return new JsonResponse([
            'success' => true,
            'message' => 'All Approved Requests Fetched.',
            'data' => $paginated
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

        $requestResources = RequestItemProfilingResourceList::collection($myApproval)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);
        return new JsonResponse([
            'success' => true,
            'message' => 'My Approvals Fetched.',
            'data' => $paginated
        ]);
    }

}
