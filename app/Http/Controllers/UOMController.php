<?php

namespace App\Http\Controllers;

use App\Models\UOM;
use App\Http\Requests\StoreUOMRequest;
use App\Http\Requests\UOMIndexRequest;
use App\Http\Requests\UpdateUOMRequest;
use App\Http\Resources\UOMResource;
use App\Utils\PaginateResourceCollection;
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

        $uoms = $query->get();
        $uomResources = UOMResource::collection($uoms);
        // $paginated = PaginateResourceCollection::paginate(collect($uomResources->toArray(request())));

        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $uomResources
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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

    public function show($id)
    {
        $uom = UOM::find($id);

        if ($uom) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully fetched.',
                'data' => new UOMResource($uom)
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found.'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UOM $uOM)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUOMRequest $request, $id)
    {
        $uom = UOM::find($id);
        $data = json_decode('{}');
        if (!is_null($uom)) {
            $uom->fill($request->validated());
            if ($uom->save()) {
                $data->message = "Successfully updated.";
                $data->success = true;
                $data->data = $uom;
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $uom = UOM::find($id);
        $data = json_decode('{}');
        if (!is_null($uom)) {
            if ($uom->delete()) {
                $data->message = "Successfully deleted.";
                $data->success = true;
                $data->data = $uom;
                return response()->json($data);
            }
            $data->message = "Failed to delete.";
            $data->success = false;
            return response()->json($data, 400);
        }
        $data->message = "Failed to delete.";
        $data->success = false;
        return response()->json($data, 404);
    }
}
