<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf;

use BeegoodIT\Pdf\Contracts\RendererContract;
use BeegoodIT\Pdf\Strategies\ChromiumStrategy;
use BeegoodIT\Pdf\Strategies\FakeStrategy;
use Illuminate\Support\ServiceProvider;

class LaravelPdfRendererServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-pdf-renderer.php',
            'laravel-pdf-renderer',
        );

        $this->app->bind(RendererContract::class, function () {
            if (config('laravel-pdf-renderer.fake') || $this->app->environment('testing')) {
                return new FakeStrategy;
            }

            return new ChromiumStrategy;
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/laravel-pdf-renderer.php' => config_path('laravel-pdf-renderer.php'),
        ], 'laravel-pdf-renderer-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-pdf-renderer');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-pdf-renderer');
    }
}
