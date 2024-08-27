<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatusType;
use App\Models\RequestItemProfiling;
use App\Http\Requests\StoreRequestItemProfilingRequest;
use App\Http\Requests\UpdateRequestItemProfilingRequest;
use App\Models\ItemProfile;
use App\Models\RequestItemProfilingItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RequestItemProfilingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = RequestItemProfiling::with('itemProfiles')->paginate(10);
        $data = json_decode('{}');
        $data->message = "Request Item Profiling successfully fetched.";
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

    /**
     * Store a newly created resource in storage.
     */

    // public function store(StoreRequestItemProfilingRequest $request)
    // {
    //     $requestItemProfiling = DB::transaction(function () use ($request) {

    //         $requestItemProfiling = RequestItemProfiling::create([
    //             'approvals' => $request->input('approvals'),
    //             'created_by' => $request->input('created_by'),
    //         ]);

    //         // $requestItemProfiling->itemProfiles()->createMany($request->input('itemProfiles'));

    //         return $requestItemProfiling;
    //     });

    //     $requestItemProfiling->load('itemProfiles');

    //     return response()->json([
    //         'message' => 'Request created successfully.',
    //         'data' => $requestItemProfiling
    //     ], 201);
    // }

    public function store(StoreRequestItemProfilingRequest $request)
{
    $attribute = $request->validated();

    $requestItemProfiling = DB::transaction(function () use ($attribute) {

        $requestItemProfiling = RequestItemProfiling::create([
            'approvals' => $attribute['approvals'],
            'created_by' => $attribute['created_by'],
        ]);

        $requestItemProfiling->itemProfiles()->createMany($attribute->input('itemProfiles'));

        // foreach ($attribute['item_profile_ids'] as $itemProfileId) {

        //     RequestItemProfilingItems::create([
        //         'request_itemprofiling_id' => $requestItemProfiling->id,
        //         'item_profile_id' => $itemProfileId,
        //     ]);
        // }

        return $requestItemProfiling;
    });

    $requestItemProfiling->load('itemProfiles');

    return response()->json([
        'message' => 'Request created successfully.',
        'data' => $requestItemProfiling
    ], 201);
}



    /**
     * Display the specified resource.
     */
    public function show(RequestItemProfiling $requestItemProfiling)
    {
        //
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
    public function update(UpdateRequestItemProfilingRequest $request, RequestItemProfiling $requestItemProfiling)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestItemProfiling $requestItemProfiling)
    {
        //
    }
}
