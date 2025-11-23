<?php

namespace BeeGoodIT\LaravelPwa;

use Illuminate\Support\ServiceProvider;

class LaravelPwaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish PWA assets
        $this->publishes([
            __DIR__.'/../public/manifest.json' => public_path('manifest.json'),
        ], 'pwa-manifest');

        $this->publishes([
            __DIR__.'/../public/sw.js' => public_path('sw.js'),
        ], 'pwa-service-worker');

        $this->publishes([
            __DIR__.'/../public/icons' => public_path('icons'),
        ], 'pwa-icons');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-pwa'),
        ], 'pwa-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-pwa');
    }
}
