<?php

namespace App\Http\Controllers;

use App\Models\RequestItemProfiling;
use App\Http\Requests\StoreRequestItemProfilingRequest;
use App\Http\Requests\UpdateRequestItemProfilingRequest;
use App\Models\ItemProfile;
use Illuminate\Support\Facades\DB;

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

    public function store(StoreRequestItemProfilingRequest $request)
    {
        $attributes = $request->validated();
        $attributes['created_by'] = auth()->user()->id;

        try {
            $requestItemProfiling = DB::transaction(function () use ($attributes) {
                $requestItemProfiling = RequestItemProfiling::create([
                    'approvals' => $attributes['approvals'],
                    'created_by' => $attributes['created_by'],
                ]);

                if (isset($attributes['item_profiles'])) {
                    foreach ($attributes['item_profiles'] as $itemProfileData) {
                        $itemProfileData['request_itemprofiling_id'] = $requestItemProfiling->id;
                        ItemProfile::create($itemProfileData);
                    }
                }

                return $requestItemProfiling;
            });

            $requestItemProfiling->load('itemProfiles');

            return response()->json([
                'message' => 'Request created successfully.',
                'data' => $requestItemProfiling,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create request.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
