<?php

namespace App\Http\Controllers;

use App\Models\UOM;
use App\Http\Requests\StoreUOMRequest;
use App\Http\Requests\UOMIndexRequest;
use App\Http\Requests\UpdateUOMRequest;
use App\Http\Resources\UOMResource;
use Illuminate\Http\JsonResponse;

class UOMController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(UOMIndexRequest $request)
    {
        $filter = $request->validated()['filter'] ?? '';
        $query = UOM::query();
        if ($filter === 'custom') {
            $query->where('is_standard', false);
            $message = 'Custom UOMs Fetched.';
        } elseif ($filter === 'standard') {
            $query->where('is_standard', true);
            $message = 'Standard UOMs Fetched.';
        } else {
            $message = 'UOMs Fetched.';
        }

        $uoms = $query->paginate(10);
        $collection = UOMResource::collection($uoms)->response()->getData(true);

        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $collection
        ]);
    }

    public function get()
    {
        $main = UOM::get();
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $main;
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUOMRequest $request)
    {
        $uomData = $request->validated();
        $uomData['is_standard'] = false;
        $uom = UOM::create($uomData);
        $response = [
            'message' => $uom ? 'Successfully saved.' : 'Save failed.',
            'success' => (bool) $uom,
            'data' => $uom ? new UOMResource($uom) : null,
        ];
        return response()->json($response, $uom ? 200 : 400);

    }

    /**
     * Display the specified resource.
     */

    public function show(UOM $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => $resource
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUOMRequest $request, UOM $resource)
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
    public function destroy(UOM $resource)
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
}
