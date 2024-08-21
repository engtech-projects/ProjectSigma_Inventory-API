<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ValidateAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $response = Http::withToken($token)->get('http://localhost:8000/api/me');
        if (!$response->successful()) {
            return response()->json(['data' => $response->json(),'message' => $response->throw()]);
        }
        return $next($request);
    }
}
