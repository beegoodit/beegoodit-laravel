<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi;

use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Capabilities\FilamentAiCapabilityProvider;
use BeegoodIT\FilamentAi\Http\Middleware\ApplyMcpRateLimits;
use BeegoodIT\FilamentAi\Http\Middleware\AuthenticateMcpToken;
use BeegoodIT\FilamentAi\Mcp\FilamentAiMcpServer;
use BeegoodIT\FilamentAi\Services\AccessService;
use BeegoodIT\FilamentAi\Services\ToolContext;
use BeegoodIT\FilamentAi\Support\McpAvailability;
use Illuminate\Support\ServiceProvider;

class FilamentAiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-ai.php', 'filament-ai');

        $this->app->singleton(CapabilityRegistry::class, fn (): CapabilityRegistry => new CapabilityRegistry);
        $this->app->singleton(AccessService::class);
        $this->app->singleton(ToolContext::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-ai');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-ai');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->app->make(CapabilityRegistry::class)
            ->register(new FilamentAiCapabilityProvider);

        $this->registerMcpServer();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-ai.php' => config_path('filament-ai.php'),
            ], 'filament-ai-config');

            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/filament-ai'),
            ], 'filament-ai-lang');

            if (method_exists($this, 'publishesMigrations')) {
                $this->publishesMigrations([
                    __DIR__.'/../database/migrations' => database_path('migrations'),
                ], 'filament-ai-migrations');
            } else {
                $this->publishes([
                    __DIR__.'/../database/migrations' => database_path('migrations'),
                ], 'filament-ai-migrations');
            }
        }
    }

    private function registerMcpServer(): void
    {
        if (! McpAvailability::packagePresent()) {
            return;
        }

        if (! class_exists(\Laravel\Mcp\Facades\Mcp::class)) {
            return;
        }

        $path = (string) config('filament-ai.mcp.path', '/ai/mcp');

        \Laravel\Mcp\Facades\Mcp::web($path, FilamentAiMcpServer::class)
            ->middleware([
                AuthenticateMcpToken::class,
                ApplyMcpRateLimits::class,
            ]);
    }
}
