<?php

namespace App\Http\Controllers;

use App\Models\RequestItemProfilingItems;
use App\Http\Requests\StoreRequestItemProfilingItemsRequest;
use App\Http\Requests\UpdateRequestItemProfilingItemsRequest;
use App\Models\RequestItemProfiling;
use Illuminate\Http\Request;

class RequestItemProfilingItemsController extends Controller
{
    public function linkToRequest(Request $request, $requestId)
    {
        $validated = $request->validate([
            'item_profile_ids' => 'required|array',
            'item_profile_ids.*' => 'exists:item_profile,id',
        ]);

        $requestProfiling = RequestItemProfiling::findOrFail($requestId);

        foreach ($validated['item_profile_ids'] as $itemProfileId) {
            $requestProfiling->requestItemprofilingItems()->create([
                'item_profile_id' => $itemProfileId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Items linked to request successfully',
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreRequestItemProfilingItemsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestItemProfilingItems $requestItemProfilingItems)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestItemProfilingItems $requestItemProfilingItems)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestItemProfilingItemsRequest $request, RequestItemProfilingItems $requestItemProfilingItems)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestItemProfilingItems $requestItemProfilingItems)
    {
        //
    }
}
