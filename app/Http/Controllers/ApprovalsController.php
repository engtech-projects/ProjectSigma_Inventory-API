<?php

namespace App\Http\Controllers;

use App\Models\Approvals;
use App\Http\Requests\StoreApprovalsRequest;
use App\Http\Requests\UpdateApprovalsRequest;
use App\Http\Resources\ApprovalResource;
use App\Utils\PaginateResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $approvals = Approvals::where('module', '=', $request->input("module"))->get();
        $collection = collect(ApprovalResource::collection($approvals));

        return new JsonResponse([
           'success' => 'true',
           'message' => 'Successfully fetched.',
           'data' => new JsonResource(PaginateResourceCollection::paginate($collection, 10))
        ]);
    }


    public function get($request)
    {
        $formRequest = Approvals::where("form", "=", $request)->first();
        if (empty($formRequest)) {
            return new JsonResponse([
                "success" => false,
                "message" => "No data found.",
            ]);
        }
        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => new ApprovalResource($formRequest)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreApprovalsRequest $request)
    {
        $main = new Approvals();
        $main->fill($request->validated());
        $data = json_decode('{}');
        $main->approvals = json_encode($request->approvals);
        if (!$main->save()) {
            $data->message = "Save failed.";
            $data->success = false;
            return response()->json($data, 400);
        }
        $data->message = "Successfully save.";
        $data->success = true;
        $data->data = $main;
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $main = Approvals::find($id);
        $data = json_decode('{}');
        if (!is_null($main)) {
            $data->message = "Successfully fetch.";
            $data->success = true;
            $data->data = $main;
            return response()->json($data);
        }
        $data->message = "No data found.";
        $data->success = false;
        return response()->json($data, 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateApprovalsRequest $request, $id)
    {
        $main = Approvals::find($id);
        $data = json_decode('{}');
        if (!is_null($main)) {
            $main->fill($request->validated());
            if ($main->save()) {
                $data->message = "Successfully update.";
                $data->success = true;
                $data->data = $main;
                return response()->json($data);
            }
            $data->message = "Update failed.";
            $data->success = false;
            return response()->json($data, 400);
        }

        $data->message = "Failed update.";
        $data->success = false;
        return response()->json($data, 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $main = Approvals::find($id);
        $data = json_decode('{}');
        if (!is_null($main)) {
            if ($main->delete()) {
                $data->message = "Successfully delete.";
                $data->success = true;
                $data->data = $main;
                return response()->json($data);
            }
            $data->message = "Failed delete.";
            $data->success = false;
            return response()->json($data, 400);
        }
        $data->message = "Failed delete.";
        $data->success = false;
        return response()->json($data, 404);
    }
}
