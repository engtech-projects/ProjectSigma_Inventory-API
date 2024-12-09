<?php

namespace App\Http\Controllers;

use App\Enums\RequestApprovalStatus;
use App\Http\Requests\StoreRequestSupplier;
use App\Http\Requests\UpdateRequestSupplier;
use App\Http\Resources\RequestSupplierResource;
use App\Models\RequestSupplier;
use App\Http\Requests\SupplierRequestFilter;
use App\Http\Resources\RequestBOMResource;
use App\Http\Resources\RequestItemProfilingResourceList;
use App\Http\Resources\RequestSupplierResourceList;
use App\Http\Resources\SupplierResource;
use App\Http\Services\RequestSupplierService;
use App\Http\Traits\UploadFileTrait;
use App\Notifications\RequestSupplierForApprovalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Traits\HasApproval;
use Illuminate\Http\Request;

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
    //
    public function index(Request $request)
    {
        $filters = [
            'company_name' => 'like',
            'type_of_ownership' => '=',
            'contact_person_name' => 'like',
            'supplier_code' => 'like',
        ];

        $query = RequestSupplier::isApproved()
            ->with('uploads')
            ->where('company_name', 'like', "%{$request->input('company_name')}%")
            ->where('type_of_ownership', 'like', "%{$request->input('type_of_ownership')}%")
            ->where('contact_person_name', 'like', "%{$request->input('contact_person_name')}%")
            ->where('supplier_code', 'like', "%{$request->input('supplier_code')}%");

        $main = $query->paginate(10);
        $collection = SupplierResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Suppliers Successfully Fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }



    public function get()
    {
        $filteredRequests = $this->requestSupplierService->getAll();
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

        DB::transaction(function () use ($validated, $request) {
            $requestSupplier = RequestSupplier::create([
                'approvals' => $validated['approvals'],
                'created_by' => $validated['created_by'],
                'request_status' => $validated['request_status'],
                'supplier_code' => $validated['supplier_code'],
                'company_name' => $validated['company_name'],
                'company_address' => $validated['company_address'],
                'company_contact_number' => $validated['company_contact_number'],
                'company_email' => $validated['company_email'],
                'contact_person_name' => $validated['contact_person_name'],
                'contact_person_number' => $validated['contact_person_number'],
                'contact_person_designation' => $validated['contact_person_designation'],
                'type_of_ownership' => $validated['type_of_ownership'],
                'nature_of_business' => $validated['nature_of_business'],
                'products_services' => $validated['products_services'],
                'classification' => $validated['classification'],
                'tin' => $validated['tin'],
                'terms_and_conditions' => $validated['terms_and_conditions'],
                'filled_by' => $validated['filled_by'],
                'filled_designation' => $validated['filled_designation'],
                'filled_date' => $validated['filled_date'],
                'requirements_complete' => $validated['requirements_complete'],
                'remarks' => $validated['remarks'],
            ]);

            if ($requestSupplier->getNextPendingApproval()) {
                $requestSupplier->notify(new RequestSupplierForApprovalNotification($request->bearerToken(), $requestSupplier));
            }
        });

        return new JsonResponse([
            'success' => true,
            'message' => 'Supplier request successfully saved.',
        ], JsonResponse::HTTP_OK);
    }

    public function show(RequestSupplier $resource)
    {
        return response()->json([
            "message" => "Successfully Fetched Supplier {$resource->company_name}.",
            "success" => true,
            "data" =>  new RequestSupplierResource($resource)
        ]);
    }

    public function update(UpdateRequestSupplier $request, RequestSupplier $resource)
    {
        $resource->fill($request->validated());
        if ($resource->save()) {
            return response()->json([
                "message" => "Supplier {$resource->company_name} Successfully Updated.",
                "success" => true,
                "data" => $resource->load('uploads')->refresh()
            ]);
        }
    }

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
        $requestResources = RequestSupplierResourceList::collection($myRequest)->response()->getData(true);

        return new JsonResponse([
            'message' => 'My Request Fetched.',
            'success' => true,
            'data' => $requestResources
        ]);
    }

    public function allRequests()
    {
        $myRequest = $this->requestSupplierService->getAllRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestSupplierResourceList::collection($myRequest)->response()->getData(true);

        return new JsonResponse([
            'message' => 'All Request Fetched.',
            'success' => true,
            'data' => $requestResources
        ]);
    }

    public function allApprovedRequests()
    {
        $myRequest = $this->requestSupplierService->getAllApprovedRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = RequestSupplierResourceList::collection($myRequest)->response()->getData(true);

        return new JsonResponse([
            'message' => 'All Approved Requests Fetched.',
            'success' => true,
            'data' => $requestResources
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

        $requestResources = RequestSupplierResourceList::collection($myApproval)->response()->getData(true);

        return new JsonResponse([
            'message' => 'My Approvals Fetched.asdf',
            'success' => true,
            'data' => $requestResources
        ]);
    }
}
