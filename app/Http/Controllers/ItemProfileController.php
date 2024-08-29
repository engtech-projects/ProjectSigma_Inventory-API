<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatusType;
use App\Models\User;
use App\Models\ItemProfile;
use App\Models\RequestItemProfiling;
use App\Models\RequestItemProfilingItems;
use App\Http\Requests\StoreItemProfileRequest;
use App\Http\Requests\UpdateItemProfileRequest;
use App\Http\Resources\ItemProfileResource;
use App\Http\Services\ItemProfileService;
use App\Notifications\RequestItemProfilingForApproval;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ItemProfileController extends Controller
{
    protected $itemProfileService;
    public function __construct(ItemProfileService $itemProfileService)
    {
        $this->itemProfileService = $itemProfileService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $itemprofile = ItemProfile::paginate(10);
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $itemprofile;
        return response()->json($data);
    }

    public function get()
    {
        $main = ItemProfile::get();
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $main;
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemProfileRequest $request)
    {
        // dd(auth()->user()->id);
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

                    $itemProfile = ItemProfile::create($itemprofileData);

                    RequestItemProfilingItems::create([
                        'item_profile_id' => $itemProfile->id,
                        'request_itemprofiling_id' => $requestItemProfiling->id,
                    ]);
                }

                $requestItemProfiling->refresh();
                // if ($requestItemProfiling->getNextPendingApproval()) {
                //     User::find($requestItemProfiling->getNextPendingApproval()['user_id'])->notify(new RequestItemProfilingForApproval($requestItemProfiling));
                // }
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
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $itemprofile = ItemProfile::find($id);
        $data = json_decode('{}');
        if (!is_null($itemprofile)) {
            $data->message = "Successfully fetched.";
            $data->success = true;
            $data->data = $itemprofile;
            return response()->json($data);
        }
        $data->message = "No data found.";
        $data->success = false;
        return response()->json($data, 404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(UpdateItemProfileRequest $request, $id)
    {
        $itemprofile = ItemProfile::find($id);
        $data = json_decode('{}');
        if (!is_null($itemprofile)) {
            $itemprofile->fill($request->validated());
            if ($itemprofile->save()) {
                $data->message = "Successfully updated.";
                $data->success = true;
                $data->data = $itemprofile;
                return response()->json($data);
            }
            $data->message = "Failed to update.";
            $data->success = false;
            return response()->json($data, 400);
        }
        $data->message = "Failed to update.";
        $data->success = false;
        return response()->json($data, 404);
    }
    public function destroy($id)
    {
        $itemprofile = ItemProfile::find($id);
        $data = json_decode('{}');
        if (!is_null($itemprofile)) {
            if ($itemprofile->delete()) {
                $data->message = "Successfully deleted.";
                $data->success = true;
                $data->data = $itemprofile;
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
        $myRequest = $this->itemProfileService->getMyRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        return new JsonResponse([
            'success' => true,
            'message' => 'Item Profile Request Fetched.',
            'data' => ItemProfileResource::collection($myRequest)
        ]);
    }

    public function myApprovals()
    {
        $myApproval = $this->itemProfileService->getMyApprovals();
        if ($myApproval->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        return new JsonResponse([
            'success' => true,
            'message' => 'Item Profile Request fetched.',
            'data' => ItemProfileResource::collection($myApproval)
        ]);
    }

}
