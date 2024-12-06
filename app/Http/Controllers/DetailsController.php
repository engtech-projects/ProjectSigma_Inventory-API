<?php

namespace App\Http\Controllers;

use App\Models\Details;
use App\Http\Requests\StoreDetailsRequest;
use App\Http\Requests\UpdateDetailsRequest;
use App\Http\Resources\BOMDetailsResource;
use Illuminate\Http\JsonResponse;

class DetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = Details::paginate(10);
        $collection = BOMDetailsResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "BOM Details Successfully fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDetailsRequest $request, Details $resource)
    {
        $saved = $resource->create($request->validated());
        return response()->json([
            'message' => $saved ? 'Item(s) successfully created.' : 'Failed to create item(S).',
            'success' => (bool) $saved,
            'data' => $saved ?? null,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Details $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => new BOMDetailsResource($resource)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDetailsRequest $request, Details $resource)
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
    public function destroy(Details $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Item(s) not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'An item(s) has successfully deleted.' : 'Failed to delete item(s).',
            'success' => $deleted,
            'data' => $resource
        ];

        return response()->json($response, $deleted ? 200 : 400);
    }
}
