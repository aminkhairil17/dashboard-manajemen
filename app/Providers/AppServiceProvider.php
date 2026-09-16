<?php

namespace App\Providers;

use App\Listeners\SyncCompanyFromHris;
use App\Support\CurrentCompany;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Syifa\KeycloakSso\Events\KeycloakLoginSucceeded;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrentCompany::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrasi driver Socialite 'keycloak' & route SSO ditangani oleh
        // package syifa/keycloak-sso — lihat KeycloakSsoServiceProvider.
        Event::listen(KeycloakLoginSucceeded::class, SyncCompanyFromHris::class);
    }
}
