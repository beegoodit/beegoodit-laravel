<?php

namespace BeegoodIT\FilamentEntryLinks;

use BeegoodIT\FilamentEntryLinks\Http\Controllers\ShowEntryLinkController;
use BeegoodIT\FilamentEntryLinks\Models\EntryLink;
use BeegoodIT\FilamentEntryLinks\Policies\EntryLinkPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FilamentEntryLinksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/filament-entry-links.php',
            'filament-entry-links'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/filament-entry-links.php' => config_path('filament-entry-links.php'),
        ], 'filament-entry-links-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/filament-entry-links'),
        ], 'filament-entry-links-views');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-entry-links');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/filament-entry-links'),
        ], 'filament-entry-links-lang');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'filament-entry-links');

        $this->registerRoutes();

        Gate::policy(EntryLink::class, EntryLinkPolicy::class);
    }

    protected function registerRoutes(): void
    {
        $prefix = trim((string) config('filament-entry-links.route_prefix', 'link'), '/');

        if ($prefix === '') {
            return;
        }

        /** @var array<int, string> $middleware */
        $middleware = config('filament-entry-links.middleware', ['web']);

        Route::middleware($middleware)
            ->get($prefix.'/{segment}', ShowEntryLinkController::class)
            ->name('filament-entry-links.show');
    }
}
