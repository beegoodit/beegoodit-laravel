<?php

namespace BeegoodIT\FilamentSocialLinks;

use Illuminate\Support\ServiceProvider;

class FilamentSocialLinksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'filament-social-links');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-social-links');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/seeders' => database_path('seeders'),
            ], 'filament-social-links-seeders');
        }
    }

    public function register(): void
    {
        //
    }
}
