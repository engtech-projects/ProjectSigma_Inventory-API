<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthResource;

class AuthController extends Controller
{
    public function show()
    {
        return new AuthResource(auth()->user());
    }
}
