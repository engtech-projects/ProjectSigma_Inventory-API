<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateToken;
use App\Http\Services\ApiServices\HrmsService;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $request = User::get();

        return response()->json([
            'message' => 'Users Successfully Fetched.',
            'success' => true,
            'data' => $request,
        ]);
    }
    public function store(ValidateToken $request)
    {
        $request = $request->validated();
        $token = $request['token'] ?? null;
        $users = HrmsService::getUsers($token);

        if ($users === false) {
            return response()->json([
                'message' => 'Failed to fetch users from HRMS API.',
                'success' => false,
            ]);
        }
        foreach ($users as $user) {
            User::updateOrCreate(
                [
                    'hrms_id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'email_verified_at' => $user['email_verified_at'],
                    'type' => $user['type'],
                    'password' => $user[''],
                    'accessibilities' => json_encode($user['accessibilities']),
                ]
            );
        }
        return response()->json([
            'message' => 'Users synchronized successfully.',
            'success' => true,
            'data' => $users,
        ]);
    }
}
