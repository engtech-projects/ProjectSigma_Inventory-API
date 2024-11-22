<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestSupplierUpload;
use App\Http\Resources\RequestSupplierUploadResource;
use App\Models\RequestSupplierUpload;
use App\Utils\PaginateResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestSupplierUploadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = RequestSupplierUpload::get();
        $paginated = PaginateResourceCollection::paginate($main);
        $data = json_decode('{}');
        $data->message = "Supplier Documents Successfully Fetched.";
        $data->success = true;
        $data->data = $paginated;
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function get()
    {
        $main = RequestSupplierUpload::isApproved()->get();
        $requestResources = RequestSupplierUploadResource::collection($main)->collect();
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
    // public function store(Request $request)
    // {
    //     //
    // }

    public function store(StoreRequestSupplierUpload $request)
    {
        $attributes = $request->validated();
        DB::transaction(function () use ($attributes) {
            $upload = RequestSupplierUpload::create($attributes);
        });
        return response()->json(['message' => 'Upload successfully created.', 'success' => true, 'data' => new RequestSupplierUploadResource($upload),]);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestSupplierUpload $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => $resource
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestSupplierUpload $requestSupplierUpload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestSupplierUpload $requestSupplierUpload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestSupplierUpload $requestSupplierUpload)
    {
        //
    }
}
