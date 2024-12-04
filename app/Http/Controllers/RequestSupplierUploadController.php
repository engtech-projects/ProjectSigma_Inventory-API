<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestSupplierUpload;
use App\Http\Requests\UpdateRequestSupplierUpload;
use App\Http\Resources\RequestSupplierUploadResource;
use App\Http\Traits\UploadFileTrait;
use App\Models\RequestSupplierUpload;
use App\Utils\PaginateResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RequestSupplierUploadController extends Controller
{
    use UploadFileTrait;
    public const EMPLOYEEDIR = "supplier_folder/";

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = RequestSupplierUpload::get();
        // $paginated = PaginateResourceCollection::paginate($main);
        $data = json_decode('{}');
        $data->message = "Supplier Documents Successfully Fetched.";
        $data->success = true;
        $data->data = $main;
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
    public function store(StoreRequestSupplierUpload $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $attachments = $validated['attachments'];
            $supplierId = $validated['request_supplier_id'];

            $savedAttachments = [];

            foreach ($attachments as $attachment) {
                $fileLocation = $this->uploadFile(
                    $attachment['file'],
                    RequestSupplierUpload::SUPPLIER_ATTACHMENTS_DIR
                );

                $upload = new RequestSupplierUpload();
                $upload->request_supplier_id = $supplierId;
                $upload->attachment_name = $attachment['attachment_name'];
                $upload->file_location = $fileLocation;
                $upload->save();

                $savedAttachments[] = $upload;
            }

            DB::commit();

            return response()->json([
                "message" => "Successfully uploaded files.",
                "success" => true,
                "data" => $savedAttachments,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "message" => "Failed to upload files. " . $e->getMessage(),
                "success" => false,
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(RequestSupplierUpload $upload)
    {
        if (!$upload) {
            return response()->json([
                "message" => "No data found.",
                "success" => false,
                "data" => null
            ], 404);
        }
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => $upload
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(UpdateRequestSupplierUpload $request, RequestSupplierUpload $upload)
    // {
    //     $upload->fill($request->validated());
    //     if ($upload->save()) {
    //         return response()->json([
    //             "message" => "Successfully updated.",
    //             "success" => true,
    //             "data" => $upload->refresh()
    //         ]);
    //     }
    //     return response()->json([
    //         "message" => "Failed to update.",
    //         "success" => false,
    //         "data" => $upload
    //     ], 400);
    // }

    public function update(UpdateRequestSupplierUpload $request, RequestSupplierUpload $upload)
{
    // Fill the model with all request data
    $update = $upload->fill($request->all());
    if ($update->save()) {
        // Handling file uploads if any file is provided
        if ($request->hasFile('attachments.0.file')) {
            $file = $request->file('attachments.0.file');
            $originalName = $file->getClientOriginalName();

            // Replace the old file with the new file
            $upload->file_location = $this->replaceUploadFile($upload->file_location, $file, RequestSupplierUpload::SUPPLIER_ATTACHMENTS_DIR);

            // Update the file location in the model
            $upload->save();
        }

        return response()->json([
            'message' => 'Successfully updated.',
            'success' => true,
            'data' => $upload->refresh(),
        ]);
    }

    return response()->json([
        'message' => 'Failed to update.',
        'success' => false,
        'data' => $upload,
    ], 400);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestSupplierUpload $upload)
    {
        if (!$upload) {
            return response()->json([
                'message' => 'Supplier Attachment not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $upload->delete();

        $response = [
            'message' => $deleted ? 'Supplier Attachment successfully deleted.' : 'Failed to delete Supplier Attachment.',
            'success' => $deleted,
            'data' => $upload
        ];
        return response()->json($response, $deleted ? 200 : 400);
    }
}
