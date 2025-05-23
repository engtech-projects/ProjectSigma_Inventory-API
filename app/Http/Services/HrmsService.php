<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HrmsService
{
    protected $apiUrl;
    protected $authToken;

    public function __construct($authToken)
    {
        $this->authToken = $authToken;
        $this->apiUrl = config('services.url.hrms_api_url');
    }

    public static function setNotification($token, $userid, $notificationData)
    {
        if (gettype($notificationData) == "array") {
            $notificationData = json_encode($notificationData);
        }
        $response = Http::withToken(token: $token)
            ->acceptJson()
            ->withBody($notificationData)
            ->post(config('services.url.hrms_api_url') . "/api/notifications/services-notify/{$userid}");
        if (!$response->successful()) {
            return false;
        }

        // return $response->json();
    }

    public static function formatApprovals($token, $approvals)
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->withQueryParameters($approvals)
            ->get(config('services.url.hrms_api_url') . "/api/services/format-approvals");
        if (!$response->successful()) {
            return $approvals;
        }
        return $response->json()["data"];
    }
    public static function getEmployeeDetails($token, $user_ids)
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->get(config('services.url.hrms_api_url') . '/api/services/user-employees', [
                'user_ids' => $user_ids
            ]);

        if (!$response->successful()) {
            return false;
        }

        return $response->json("data");
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
        $employees = array_map(fn($employee) => [
            "hrms_id" => $employee['id'],
            "first_name" => $employee['first_name'],
            "middle_name" => $employee['middle_name'],
            "family_name" => $employee['family_name'],
            "name_suffix" => $employee['name_suffix'],
            "nick_name" => $employee['nick_name'],
            "gender" => $employee['gender'],
            "date_of_birth" => $employee['date_of_birth'],
            "place_of_birth" => $employee['place_of_birth'],
            "citizenship" => $employee['citizenship'],
            "blood_type" => $employee['blood_type'],
            "civil_status" => $employee['civil_status'],
            "date_of_marriage" => $employee['date_of_marriage'],
            "telephone_number" => $employee['telephone_number'],
            "mobile_number" => $employee['mobile_number'],
            "email" => $employee['email'],
            "religion" => $employee['religion'],
            "weight" => $employee['weight'],
            "height" => $employee['height'],
        ], $employees['data']);

        Employee::upsert(
            $employees,
            [
                'hrms_id',
            ],
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
            ]
        );
        return true;
    }

    public function syncUsers()
    {
        $users = $this->getAllUsers();
        $users = array_map(fn($user) => [
            "hrms_id" => $user['id'],
            "type" => $user['type'],
            "accessibilities" => $user['accessibilities'],
            "name" => $user['name'],
            "email" => $user['email'],
            "email_verified_at" => $user['email_verified_at'],
            "password" => Hash::make(Str::random(10)),
        ], $users);

        User::upsert(
            $users,
            [
                'hrms_id',
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
        $departments = array_map(fn($department) => [
            "hrms_id" => $department['id'],
            "department_name" => $department['department_name'],
        ], $departments);

        Department::upsert(
            $departments,
            [
                'hrms_id',
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
            ->acceptJson()
            ->get($this->apiUrl.'/api/employee/list');
        if (! $response->successful()) {
            return [];
        }
        return $response->json();
    }

    public function getAllUsers()
    {
        $response = Http::withToken($this->authToken)
            ->withUrlParameters([
                "paginate" => false,
                "sort" => "asc"
            ])
            ->acceptJson()
            ->get($this->apiUrl.'/api/employee/users-list');
        if (! $response->successful()) {
            return [];
        }
        return $response->json();
    }

    public function getAllDepartments()
    {
        $response = Http::withToken($this->authToken)
            ->withUrlParameters([
                "paginate" => false,
                "sort" => "asc"
            ])
            ->acceptJson()
            ->get($this->apiUrl.'/api/department/list');
        if (! $response->successful()) {
            return [];
        }
        return $response->json();
    }

}
