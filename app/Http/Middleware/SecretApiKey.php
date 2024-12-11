<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class SecretApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientSecretKey = $request->bearerToken(); // Use Bearer token to skip setting up allowing new header name for secret key
        $secretKey = config('services.sigma.secret_key');
        if ($clientSecretKey === $secretKey) {
            return $next($request);
        }
        return new JsonResponse([
            'success' => false,
            'message' => 'Access denied. Wrong SECRET KEY',
        ], JsonResponse::HTTP_FORBIDDEN);
        // Authorization header follows bearer token format
        // EXAMPLE OF HOW TO ACCESS API WITH HEADER
        // $response = Http::withHeaders([
        //         'User-Agent'=> 'Thunder Client (https://www.thunderclient.com)',
        //         'Accept'=> 'application/json',
        //         'Authorization'=> 'Bearer 123123',
        //     ])
        //     ->get('https://projectsigma_hrms-api.test/api/sigma/user-employees?user_ids[0]=2&user_ids[1]=4');
        // echo $response->body();
    }
}
