<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestSupplierUpload;
use App\Http\Resources\RequestSupplierResource;
use App\Http\Resources\RequestSupplierUploadResource;
use App\Http\Traits\UploadFileTrait;
use App\Models\RequestSupplierUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RequestSupplierUploadController extends Controller
{
    use UploadFileTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = RequestSupplierUpload::paginate(10);
        $collection = RequestSupplierUploadResource::collection($main)->response()->getData(true);
        return new JsonResponse([
            "success" => true,
            "message" => "Suppliers Successfully Fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    public function get()
    {
        $main = RequestSupplierUpload::isApproved()->get();
        $collection = RequestSupplierUploadResource::collection($main)->response()->getData(true);
        return new JsonResponse([
            "success" => true,
            "message" => "Suppliers Successfully Fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    public function store(StoreRequestSupplierUpload $request)
    {
        $validated = $request->validated();
        $fileUploaded = DB::transaction(function () use ($validated) {
            $fileLocation = $this->uploadFile(
                $validated['file'],
                RequestSupplierUpload::SUPPLIER_ATTACHMENTS_DIR
            );
            $upload = new RequestSupplierUpload();
            $upload->request_supplier_id = $validated['request_supplier_id'];
            $upload->attachment_name = $validated['attachment_name'];
            $upload->file_location = $fileLocation;
            if ($upload->save()) {
                return $upload;
            } else {
                return null;
            }
        });
        if (!$fileUploaded) {
            return response()->json([
                "message" => "Failed to upload file.",
                "success" => false,
            ], 500);
        }
        return response()->json([
            "message" => "Successfully uploaded file.",
            "success" => true,
            "data" => $fileUploaded,
        ], 201);
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
     * Remove the specified resource from storage.
     */
    public function destroy(RequestSupplierUpload $upload)
    {
        $supplier = $upload->requestSupplier;
        if (Storage::disk('public')->exists($upload->file_location)) {
            Storage::disk('public')->delete($upload->file_location);
            $directoryPath = dirname($upload->file_location);
            if (Storage::disk('public')->directories($directoryPath) == [] && Storage::disk('public')->files($directoryPath) == []) {
                Storage::disk('public')->deleteDirectory($directoryPath);
            }
        }
        $deleted = $upload->delete();
        return RequestSupplierResource::make($supplier)->additional([
            'message' => $deleted ? 'Supplier Attachment and file successfully deleted.' : 'Failed to delete Supplier Attachment.',
            'success' => $deleted,
        ])
        ->response()
        ->setStatusCode($deleted ? 200 : 500);
    }
}
