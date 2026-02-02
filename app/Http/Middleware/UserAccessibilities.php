<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpFoundation\Response;

class UserAccessibilities
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $allowedAccess): Response
    {
        $allowedAccessibilities = explode("|", $allowedAccess);
        foreach ($allowedAccessibilities as $ability) {
            if (auth()->user()->cannot($ability)) {
                throw new AuthorizationException('This action is unauthorized. Access denied.');
            }
        }
        return $next($request);
    }
}
