<?php

namespace BeegoodIT\FilamentTenancyRoles;

use Illuminate\Support\ServiceProvider;

class FilamentTenancyRolesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Load translations if we add them later
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-tenancy-roles');

        if ($this->app->runningInConsole()) {
            // Future migrations can be added here
        }
    }
}
