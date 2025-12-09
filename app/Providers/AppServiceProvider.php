<?php

namespace App\Providers;

use App\Enums\OwnerType;
use App\Models\SetupDepartments;
use App\Models\SetupProjects;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.force_https', false)) {
            URL::forceScheme('https');
        }
        Relation::morphMap([
            OwnerType::PROJECT->value => SetupProjects::class,
            OwnerType::DEPARTMENT->value => SetupDepartments::class,
        ]);
    }
}
