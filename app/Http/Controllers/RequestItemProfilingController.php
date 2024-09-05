<?php

namespace App\Http\Controllers;

use App\Enums\ActiveStatus;
use App\Enums\RequestStatusType;
use App\Http\Requests\StoreItemProfileRequest;
use App\Models\RequestItemProfiling;
use App\Http\Requests\UpdateRequestItemProfilingRequest;
use App\Http\Resources\RequestItemProfilingResource;
use App\Http\Services\RequestItemProfilingService;
use App\Models\ItemProfile;
use App\Models\RequestItemProfilingItems;
use App\Models\User;
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    public function get()
    {
        $main = RequestItemProfiling::with('itemProfiles')->get();

        $requestResources = RequestItemProfilingResource::collection($main)->collect();
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

    public function store(StoreItemProfileRequest $request)
    {
        $attributes = $request->validated();
        $attributes['request_status'] = RequestStatusType::PENDING->value;
        $attributes['created_by'] = auth()->user()->id;

        try {
            DB::transaction(function () use ($attributes) {
                $requestItemProfiling = RequestItemProfiling::create([
                    'approvals' => $attributes['approvals'],
                    'created_by' => $attributes['created_by'],
                    'request_status' => $attributes['request_status'],
                ]);

                foreach ($attributes['item_profiles'] as $itemprofileData) {
                    $itemProfileData['request_itemprofiling_id'] = $requestItemProfiling->id;
                    $itemProfileData['active_status'] = ActiveStatus::ACTIVE;

                    $itemProfile = ItemProfile::create($itemprofileData);

                    RequestItemProfilingItems::create([
                        'item_profile_id' => $itemProfile->id,
                        'request_itemprofiling_id' => $requestItemProfiling->id,
                    ]);
                }

                $requestItemProfiling->refresh();
                if ($nextPendingApproval = $requestItemProfiling->getNextPendingApproval()) {
                    $userId = $nextPendingApproval['user_id'];
                    $user = User::find($userId);
                }

            });
            return new JsonResponse([
                'success' => true,
                'message' => 'Item Profiles Successfully Saved.',
                // 'data' => $attributes['item_profiles'],
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save Item Profiles.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestItemProfiling $requestId)
    {
        // return response()->json([
        //     "message" => "Successfully fetched.",
        //     "success" => true,
        //     "data" => new RequestItemProfilingResource($requestId)
        // ]);

        $requestResources = RequestItemProfilingResource::collection(collect([$requestId]))->collect();
        
        $paginated = PaginateResourceCollection::paginate($requestResources);

        // Return the response with the paginated data
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => $paginated
        ]);

    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestItemProfiling $requestItemProfiling)
    {
        //
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
    public function destroy($id)
    {
        $requestitemprofile = ItemProfile::find($id);
        $data = json_decode('{}');
        if (!is_null($requestitemprofile)) {
            if ($requestitemprofile->delete()) {
                $data->message = "Successfully deleted.";
                $data->success = true;
                $data->data = $requestitemprofile;
                return response()->json($data);
            }
            $data->message = "Failed to delete.";
            $data->success = false;
            return response()->json($data, 404);
        }
        $data->message = "Failed to delete.";
        $data->success = false;
        return response()->json($data, 404);
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

        $requestResources = RequestItemProfilingResource::collection($myRequest)->collect();
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

        $requestResources = RequestItemProfilingResource::collection($myRequest)->collect();
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

        $requestResources = RequestItemProfilingResource::collection($myRequest)->collect();
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

        $requestResources = RequestItemProfilingResource::collection($myApproval)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);
        return new JsonResponse([
            'success' => true,
            'message' => 'My Approvals Fetched.',
            'data' => $paginated
        ]);
    }

}
