<?php

namespace App\Http\Controllers;

use App\Enums\RequestApprovalStatus;
use App\Http\Requests\StoreRequestSupplier;
use App\Http\Requests\UpdateRequestSupplier;
use App\Http\Resources\RequestSupplierResource;
use App\Models\RequestSupplier;
use App\Utils\PaginateResourceCollection;
use App\Http\Requests\SupplierRequestFilter;
use App\Http\Resources\RequestItemProfilingResourceList;
use App\Http\Resources\RequestSupplierResourceList;
use App\Http\Resources\SupplierResource;
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
        $collection = collect(SupplierResource::collection($main));
        return new JsonResponse([
            "success" => true,
            "message" => "Suppliers Successfully Fetched.",
            "data" => PaginateResourceCollection::paginate($collection, 10)
        ], JsonResponse::HTTP_OK);
    }
    public function getCompanyName(SupplierRequestFilter $request)
    {
        $filters = $request->validated();
        $main = RequestSupplier::select('company_name')
            ->where('company_name', 'LIKE', "%{$filters['key']}%")
            ->distinct()
            ->get();
        return new JsonResponse([
            "success" => true,
            "message" => "Company Name Successfully Fetched.",
            "data" => $main->pluck('company_name')
        ], JsonResponse::HTTP_OK);
    }

    public function getSupplierCode(SupplierRequestFilter $request)
    {
        $filters = $request->validated();
        $main = RequestSupplier::select('supplier_code')
            ->where('supplier_code', 'LIKE', "%{$filters['key']}%")
            ->distinct()
            ->get();
        return new JsonResponse([
            "success" => true,
            "message" => "Supplier Code Successfully Fetched.",
            "data" => $main->pluck('supplier_code')
        ], JsonResponse::HTTP_OK);

    }

    public function getContactPersonName(SupplierRequestFilter $request)
    {
        $filters = $request->validated();
        $main = RequestSupplier::select('contact_person_name')
            ->where('contact_person_name', 'LIKE', "%{$filters['key']}%")
            ->distinct()
            ->get();
        return new JsonResponse([
            "success" => true,
            "message" => "Contact Person Name Successfully Fetched.",
            "data" => $main->pluck('contact_person_name')
        ], JsonResponse::HTTP_OK);
    }
    public function get(SupplierRequestFilter $request)
    {
        $filters = $request->validated();
        $filteredRequests = $this->requestSupplierService->getAll($filters);
        if ($filteredRequests->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
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
        $validated['approvals'] = $convertedData = collect($validated['approvals'])->map(function ($item) {
            $item['user_id'] = (int) $item['user_id'];
            return $item;
        });
        $attachmentNames = array_column($validated['attachments'] ?? [], 'attachment_name');
        if (count($attachmentNames) !== count(array_unique($attachmentNames))) {
            return new JsonResponse(['success' => false, 'message' => 'Duplicate attachment names are not allowed.',], JsonResponse::HTTP_BAD_REQUEST);
        }

        DB::transaction(function () use ($validated, $request) {
            $requestSupplier = RequestSupplier::create(
                $validated
            );

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

        if ($resource->request_status !== RequestApprovalStatus::APPROVED) {
            return response()->json([
                "message" => "Only approved requests can be edited.",
                "success" => false
            ], 403);
        }

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

    public function myRequests()
    {
        $myRequest = $this->requestSupplierService->getMyRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestSupplierResourceList::collection($myRequest)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);

        return new JsonResponse([
            'success' => true,
            'message' => 'My Request Fetched.',
            'data' => $paginated
        ]);
    }

    public function allRequests()
    {
        $myRequest = $this->requestSupplierService->getAll();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestSupplierResourceList::collection($myRequest)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);

        return new JsonResponse([
            'success' => true,
            'message' => 'All Request Fetched.',
            'data' => $paginated
        ]);
    }

    public function allApprovedRequests()
    {
        $myRequest = $this->requestSupplierService->getAllRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestSupplierResourceList::collection($myRequest)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);

        return new JsonResponse([
            'success' => true,
            'message' => 'All Approved Requests Fetched.',
            'data' => $paginated
        ]);
    }

    public function myApprovals()
    {
        $myApproval = $this->requestSupplierService->getMyApprovals();
        if ($myApproval->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        $requestResources = RequestSupplierResourceList::collection($myApproval)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);
        return new JsonResponse([
            'success' => true,
            'message' => 'My Approvals Fetched.',
            'data' => $paginated
        ]);
    }
}
