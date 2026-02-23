<?php

namespace BeegoodIT\FilamentPartners;

use Illuminate\Support\ServiceProvider;

class FilamentPartnersServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'filament-partners');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-partners.php' => config_path('filament-partners.php'),
            ], 'filament-partners-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'filament-partners-migrations');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-partners.php', 'filament-partners');
    }
}
