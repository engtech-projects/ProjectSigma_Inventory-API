<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Guards\AuthTokenGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->app['auth']->extend(
            'hrms-auth',
            function ($app, $name, array $config) {
                $guard = new AuthTokenGuard(
                    $app['request']
                );
                $app->refresh('request', $guard, 'setRequest');
                return $guard;
            }
        );

        Gate::define('inventory:dashboard', function ($user) {
            return $this->isGateAuthorize('inventory:dashboard', $user->accessibilities);
        });
    }
    public function isGateAuthorize($access, $accessibilites)
    {
        return in_array($access, $accessibilites);
        /* return !in_array($access,$accessibilites) ? Response::deny('Unauthorized action, access denied.') : Response::allow(); */
    }
}
