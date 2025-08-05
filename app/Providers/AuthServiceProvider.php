<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Guards\AuthTokenGuard;
use App\Models\TransactionMaterialReceiving;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\UserPolicy;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use App\Models\WarehouseTransaction;
use App\Policies\TransactionMaterialReceivingPolicy;
use App\Policies\WarehouseTransactionPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        TransactionMaterialReceiving::class => TransactionMaterialReceivingPolicy::class,
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
        //Scramble API documentation configuration
        Scramble::configure()
        ->withDocumentTransformers(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });
    }
    public function isGateAuthorize($access, $accessibilites)
    {
        return in_array($access, $accessibilites);
        /* return !in_array($access,$accessibilites) ? Response::deny('Unauthorized action, access denied.') : Response::allow(); */
    }
}
