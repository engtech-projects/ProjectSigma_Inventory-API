<?php

namespace App\Http\Services\ApiServices;

use Illuminate\Support\Facades\Http;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        $response = $this->getAllEmployees();
        $processedEmployees = array_map(fn ($employee) => [
            'id' => $employee['id'],
            'hrms_id' => $employee['id'],
            'first_name' => $employee['first_name'],
            'middle_name' => $employee['middle_name'],
            'family_name' => $employee['family_name'],
            'name_suffix' => $employee['name_suffix'],
            'nick_name' => $employee['nick_name'],
            'gender' => $employee['gender'],
            'date_of_birth' => Carbon::parse($employee['date_of_birth']),
            'place_of_birth' => $employee['place_of_birth'],
            'citizenship' => $employee['citizenship'],
            'blood_type' => $employee['blood_type'],
            'civil_status' => $employee['civil_status'],
            'date_of_marriage' => $employee['date_of_marriage'],
            'telephone_number' => $employee['telephone_number'],
            'mobile_number' => $employee['mobile_number'],
            'email' => $employee['email'],
            'religion' => $employee['religion'],
            'weight' => $employee['weight'],
            'height' => $employee['height'],
        ], $response);

        Employee::upsert(
            $processedEmployees,
            ['id'],
            [
                'id',
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
            ]
        );
        return true;
    }

    public function syncUsers()
    {
        $users = $this->getAllUsers();
        $users = array_map(fn ($user) => [
            "id" => $user['id'],
            "hrms_id" => $user['id'],
            "type" => $user['type'],
            "accessibilities" => "",
            "name" => $user['name'],
            "email" => $user['email'],
            "email_verified_at" => $user['email_verified_at'],
            "password" => Hash::make(Str::random(10)),
        ], $users);
        User::upsert(
            $users,
            [
                'id',
            ],
            [
                'hrms_id',
                'type',
                'accessibilities',
                'name',
                'email',
                'email_verified_at',
                'password',
            ]
        );
        return true;
    }

    public function syncDepartments()
    {
        $departments = $this->getAllDepartments();
        $departments = array_map(fn ($department) => [
            "id" => $department['id'],
            "hrms_id" => $department['id'],
            "department_name" => $department['department_name'],
        ], $departments);

        Department::upsert(
            $departments,
            [
                'id',
            ],
            [
                'hrms_id',
                'department_name',
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
