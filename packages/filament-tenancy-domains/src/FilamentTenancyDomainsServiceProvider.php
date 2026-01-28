<?php

namespace BeegoodIT\FilamentTenancyDomains;

use Illuminate\Support\ServiceProvider;

class FilamentTenancyDomainsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-tenancy-domains');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-tenancy-domains');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            $this->publishes([
                __DIR__.'/../config/filament-tenancy-domains.php' => config_path('filament-tenancy-domains.php'),
            ], 'filament-tenancy-domains-config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-tenancy-domains.php', 'filament-tenancy-domains');
    }
}
