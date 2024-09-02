<?php

namespace App\Providers;

use App\Enums\ApprovalModels;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Route::bind('model', function ($value, $route) {
            $modelName = $route->parameter('modelName');
            $getModel = $this->getModelClass($modelName);
            return $getModel::findOrfail($value);
        });


        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
    private function getModelClass($modelName)
    {
        $modelHasApprovals = ApprovalModels::toArray();
        try {
            array_key_exists($modelName, $modelHasApprovals);
            return $modelHasApprovals[$modelName];
        } catch (\Exception $e) {
            throw new NotFoundHttpException("Resource not found");
        }
    }
}
