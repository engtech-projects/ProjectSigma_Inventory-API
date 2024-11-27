<?php

namespace App\Http\Controllers;

use App\Enums\RequestApprovalStatus;
use App\Http\Requests\StoreRequestSupplier;
use App\Http\Requests\UpdateRequestSupplier;
use App\Http\Resources\RequestSupplierResource;
use App\Models\RequestSupplier;
use App\Utils\PaginateResourceCollection;
use App\Http\Requests\SupplierRequestFilter;
use App\Http\Services\RequestSupplierService;
use App\Http\Traits\UploadFileTrait;
use App\Notifications\RequestSupplierForApprovalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Traits\HasApproval;

class RequestSupplierController extends Controller
{
    use UploadFileTrait;
    use HasApproval;
    protected $requestSupplierService;
    public function __construct(RequestSupplierService $requestSupplierService)
    {
        $this->requestSupplierService = $requestSupplierService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = RequestSupplier::isApproved()->with('uploads')->get();
        $paginated = PaginateResourceCollection::paginate($main);
        $data = json_decode('{}');
        $data->message = "Suppliers Successfully Fetched.";
        $data->success = true;
        $data->data = $paginated;
        $data->uploads = $main->pluck('uploads')->flatten();
        return response()->json($data);
    }

    public function get(SupplierRequestFilter $request)
    {
        // $main = RequestSupplier::with('uploads')->get();
        // $requestResources = RequestSupplierResource::collection($main)->collect();

        // return response()->json([
        //     'message' => 'Successfully fetched.',
        //     'success' => true,
        //     'data' => $requestResources,
        // ]);

        $filters = $request->validated();
        $filteredRequests = $this->requestSupplierService->getAll($filters);
        if ($filteredRequests->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        // $paginated = PaginateResourceCollection::paginate($filteredRequests);
        return new JsonResponse([
            'success' => true,
            'message' => 'My Request Fetched.',
            'data' => $filteredRequests
        ]);
    }

    public function store(StoreRequestSupplier $request)
    {
        $validated = $request->validated();
        $validated['request_status'] = RequestApprovalStatus::PENDING;
        $validated['created_by'] = auth()->user()->id;

        DB::transaction(function () use ($validated, $request) {
            $fields = array_merge($validated, [
                'request_status' => RequestApprovalStatus::PENDING,
                'created_by' => auth()->user()->id,
            ]);
            $requestSupplier = RequestSupplier::create($fields);

            foreach ($validated['attachments'] ?? [] as $attachmentData) {
                $filePath = $this->uploadFile(
                    $attachmentData['file'],
                    $attachmentData['attachment_name']
                );
                $requestSupplier->uploads()->create([
                    'attachment_name' => $attachmentData['attachment_name'],
                    'file_location' => $filePath,
                ]);
            }

            if ($requestSupplier->getNextPendingApproval()) {
                $requestSupplier->notify(new RequestSupplierForApprovalNotification($request->bearerToken(), $requestSupplier));
            }
        });
        return new JsonResponse([
            'success' => true,
            'message' => 'Supplier request successfully saved.',
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestSupplier $resource)
    {
        return response()->json([
            "message" => "Successfully Fetched Supplier {$resource->company_name}.",
            "success" => true,
            "data" => $resource->load('uploads')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestSupplier $request, RequestSupplier $resource)
    {
        $updated = false;

        DB::transaction(function () use ($request, $resource, &$updated) {
            $resource->fill($request->validated());
            $updated = $resource->save();
        });

        if ($updated) {
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
    public function destroy(RequestSupplier $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Supplier not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'Supplier successfully deleted.' : 'Failed to delete a Supplier.',
            'success' => $deleted,
            'data' => $resource
        ];

        return response()->json($response, $deleted ? 200 : 400);
    }

    public function myRequests(SupplierRequestFilter $request)
    {
        $filters = $request->validated();
        $filteredRequests = $this->requestSupplierService->getMyRequest($filters);
        if ($filteredRequests->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        $paginated = PaginateResourceCollection::paginate($filteredRequests);
        return new JsonResponse([
            'success' => true,
            'message' => 'My Request Fetched.',
            'data' => $paginated
        ]);
    }

    public function allRequests(SupplierRequestFilter $request)
    {
        $filters = $request->validated();
        $filteredRequests = $this->requestSupplierService->getAllRequests($filters);
        if ($filteredRequests->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        $paginated = PaginateResourceCollection::paginate($filteredRequests);
        return new JsonResponse([
            'success' => true,
            'message' => 'All Requests Fetched.',
            'data' => $paginated
        ]);
    }

    public function allApprovedRequests(SupplierRequestFilter $request)
    {
        $filters = $request->validated();
        $myRequest = $this->requestSupplierService->getAllApprovedRequest($filters);

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $paginated = PaginateResourceCollection::paginate($myRequest);

        return new JsonResponse([
            'success' => true,
            'message' => 'All Approved Requests Fetched.',
            'data' => $paginated
        ]);
    }


    public function myApprovals(SupplierRequestFilter $request)
    {
        $filters = $request->validated();
        $myApproval = $this->requestSupplierService->getMyApprovals($filters);
        if ($myApproval->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        $paginated = PaginateResourceCollection::paginate($myApproval);
        return new JsonResponse([
            'success' => true,
            'message' => 'My Approvals Fetched.',
            'data' => $paginated
        ]);
    }
}
