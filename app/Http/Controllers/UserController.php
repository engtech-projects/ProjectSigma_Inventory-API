<?php

namespace App\Http\Controllers;

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
    public function store()
    {
        return response()->json([
            'message' => 'Moved.',
            'success' => false,
        ], 301)
        ->header('Location', '/api/setup/sync/hrms/employees');
    }
}
