<?php

namespace BeegoodIT\LaravelCookieConsent;

use BeegoodIT\LaravelCookieConsent\Livewire\CookieConsent;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LaravelCookieConsentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cookie-consent.php',
            'cookie-consent'
        );
    }

    public function boot(): void
    {
        // Register Livewire component
        Livewire::component('cookie-consent', CookieConsent::class);

        // Publish config
        $this->publishes([
            __DIR__.'/../config/cookie-consent.php' => config_path('cookie-consent.php'),
        ], 'cookie-consent-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/cookie-consent'),
        ], 'cookie-consent-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cookie-consent');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'cookie-consent');

        // Publish translations
        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/cookie-consent'),
        ], 'cookie-consent-lang');
    }
}
