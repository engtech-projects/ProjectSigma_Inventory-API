<?php

namespace App\Http\Controllers;

use App\Models\RequestItemProfiling;
use App\Http\Requests\StoreRequestItemProfilingRequest;
use App\Http\Requests\UpdateRequestItemProfilingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RequestItemProfilingController extends Controller
{
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
    // public function store(StoreRequestItemProfilingRequest $request)
    // {
    //     $attributes = $request->validated();

    //     try {
    //         DB::transaction(function () use ($attributes) {
    //             $requestProfiling = RequestItemprofiling::create($attributes);

    //             foreach ($attributes['items'] as $item) {
    //                 $requestProfiling->items()->create($item);
    //             }
    //         });

    //         return response()->json(['message' => 'Request successfully created!'], 201);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Failed to create request.', 'error' => $e->getMessage()], 500);
    //     }
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'approvals' => 'required|json',
            'created_by' => 'required|string',
        ]);

        $requestProfiling = RequestItemprofiling::create($validated);

        return response()->json([
            'message' => 'Request Item Profiling created successfully!',
            'data' => $requestProfiling,
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
