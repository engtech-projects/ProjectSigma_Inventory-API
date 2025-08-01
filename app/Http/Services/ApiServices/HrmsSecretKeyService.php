<?php

namespace App\Http\Services\ApiServices;

use Illuminate\Support\Facades\Http;
use App\Models\SetupDepartments;
use App\Models\SetupEmployees;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class HrmsSecretKeyService
{
    protected $apiUrl;
    protected $authToken;

    public function __construct()
    {
        $this->apiUrl = config('services.url.hrms_api_url');
        $this->authToken = config('services.sigma.secret_key');
        if (empty($this->authToken)) {
            throw new \InvalidArgumentException('SECRET KEY is not configured');
        }
        if (empty($this->apiUrl)) {
            throw new \InvalidArgumentException('Projects API URL is not configured');
        }
    }

    public static function getDepartments($token)
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->get(config('services.url.hrms_api_url') . '/api/department/list/v2');

        if (!$response->successful()) {
            return false;
        }
        return $response->json("data");
    }
    public static function getUsers($token)
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->get(config('services.url.hrms_api_url') . '/api/users');

        if (!$response->successful()) {
            return false;
        }
        return $response->json("data");
    }
    public static function getEmployees($token)
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->get(config('services.url.hrms_api_url') . '/api/employee/resource');

        if (!$response->successful()) {
            return false;
        }
        return $response->json("data");
    }

    public function syncAll()
    {
        $syncEmployees = $this->syncEmployees();
        $syncUsers = $this->syncUsers();
        $syncDepartments = $this->syncDepartments();

        if ($syncEmployees && $syncUsers && $syncDepartments) {
            return $syncDepartments;
        }

        return false;
    }

    public function syncEmployees()
    {
        $employees = $this->getAllEmployees();

        SetupEmployees::upsert(
            $employees,
            ['id'],
            [
                'first_name',
                'middle_name',
                'family_name',
                'name_suffix',
                'nick_name',
                'gender',
                'date_of_birth',
                'place_of_birth',
                'citizenship',
                'blood_type',
                'civil_status',
                'date_of_marriage',
                'telephone_number',
                'mobile_number',
                'email',
                'religion',
                'weight',
                'height',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        );
        return true;
    }

    public function syncUsers()
    {
        $users = $this->getAllUsers();
        User::upsert(
            $users,
            [
                'id',
            ],
            [
                "name",
                "email",
                "email_verified_at",
                "password",
                "remember_token",
                "type",
                "accessibilities",
                "employee_id",
                "created_at",
                "updated_at",
                "deleted_at",
            ]
        );
        return true;
    }

    public function syncDepartments()
    {
        $departments = $this->getAllDepartments();
        $departments = array_map(fn ($department) => [
            "id" => $department['id'],
            "code" => $department['code'],
            "department_name" => $department['department_name'],
            "created_at" => $department['created_at'],
            "updated_at" => $department['updated_at'],
            "deleted_at" => $department['deleted_at'],
        ], $departments);
        SetupDepartments::upsert(
            $departments,
            [
                'id',
            ],
            [
                'code',
                'department_name',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        );
        return true;
    }

    public function getAllEmployees()
    {
        $response = Http::withToken($this->authToken)
            ->withUrlParameters([
                'paginate' => false,
                'sort' => 'asc',
            ])
            ->acceptJson()
            ->get($this->apiUrl . '/api/sigma/sync-list/employee');

        if (!$response->successful()) {
            return [];
        }

        return $response->json("data") ?: [];
    }

    public function getAllUsers()
    {
        $response = Http::withToken($this->authToken)
            ->withUrlParameters([
                "paginate" => false,
                "sort" => "asc"
            ])
            ->acceptJson()
            ->get($this->apiUrl . '/api/sigma/sync-list/user');
        if (!$response->successful()) {
            Log::channel("HrmsService")->error('Failed to fetch users from monitoring API', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }
        $data = $response->json();
        if (!isset($data['data']) || !is_array($data['data'])) {
            Log::channel("HrmsService")->warning('Unexpected response format from users API', ['response' => $data]);
            return [];
        }
        return $data['data'];
    }

    public function getAllDepartments()
    {
        $response = Http::withToken($this->authToken)
            ->withUrlParameters([
                "paginate" => false,
                "sort" => "asc"
            ])
            ->acceptJson()
            ->get($this->apiUrl . '/api/sigma/sync-list/department');
        if (!$response->successful()) {
            Log::channel("HrmsService")->error('Failed to fetch departments from monitoring API', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }
        $data = $response->json();
        if (!isset($data['data']) || !is_array($data['data'])) {
            Log::channel("HrmsService")->warning('Unexpected response format from departments API', ['response' => $data]);
            return [];
        }
        return $data['data'];
    }
}
