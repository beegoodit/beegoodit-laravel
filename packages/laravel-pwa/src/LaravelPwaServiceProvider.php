<?php

namespace BeeGoodIT\LaravelPwa;

use BeeGoodIT\LaravelPwa\Channels\WebPushChannel;
use BeeGoodIT\LaravelPwa\Console\GenerateVapidKeysCommand;
use BeeGoodIT\LaravelPwa\Services\PushNotificationService;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelPwaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish PWA assets
        $this->publishes([
            __DIR__ . '/../public/manifest.json' => public_path('manifest.json'),
        ], 'pwa-manifest');

        $this->publishes([
            __DIR__ . '/../public/sw.js' => public_path('sw.js'),
        ], 'pwa-service-worker');

        $this->publishes([
            __DIR__ . '/../public/icons' => public_path('icons'),
        ], 'pwa-icons');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-pwa'),
        ], 'pwa-views');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-pwa');

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/pwa.php' => config_path('pwa.php'),
        ], 'pwa-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/create_push_subscriptions_table.php.stub' => database_path('migrations/' . date('Y_m_d_His') . '_create_push_subscriptions_table.php'),
        ], 'pwa-migrations');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/laravel-pwa'),
        ], 'pwa-lang');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'laravel-pwa');

        // Publish JavaScript
        $this->publishes([
            __DIR__ . '/../resources/js/push-notifications.js' => public_path('js/push-notifications.js'),
        ], 'pwa-js');

        // Publish CSS
        $this->publishes([
            __DIR__ . '/../resources/css/push-prompt.css' => public_path('css/push-prompt.css'),
        ], 'pwa-css');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateVapidKeysCommand::class,
                \BeeGoodIT\LaravelPwa\Console\SendPushNotificationCommand::class,
            ]);
        }

        // Register routes
        $this->registerRoutes();

        // Register notification channel
        $this->registerNotificationChannel();

        // Register Blade components & directives
        $this->registerBladeComponents();
        $this->registerBladeDirectives();
    }

    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/pwa.php', 'pwa');

        // Register push notification service
        $this->app->singleton(PushNotificationService::class, fn($app) => new PushNotificationService);
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'api',
            'middleware' => config('pwa.push.middleware', ['web']),
        ], function (): void {
            Route::post('/push-subscriptions', [
                \BeeGoodIT\LaravelPwa\Http\Controllers\PushSubscriptionController::class,
                'store',
            ])->name('push-subscriptions.store');

            Route::delete('/push-subscriptions', [
                \BeeGoodIT\LaravelPwa\Http\Controllers\PushSubscriptionController::class,
                'destroy',
            ])->name('push-subscriptions.destroy');
        });
    }

    protected function registerNotificationChannel(): void
    {
        Notification::resolved(function (ChannelManager $service): void {
            $service->extend('webPush', fn($app) => $app->make(WebPushChannel::class));
        });
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('pwaHead', fn() => "<?php echo view('laravel-pwa::partials.pwa-meta')->render(); ?>");

        Blade::directive('pwaScripts', fn() => "<?php echo \"<script src='\" . asset('js/push-notifications.js') . \"'></script>\"; ?>");

        Blade::directive('pwaStyles', fn() => "<?php echo \"<link rel='stylesheet' href='\" . asset('css/push-prompt.css') . \"'>\"; ?>");
    }

    protected function registerBladeComponents(): void
    {
        Blade::component('laravel-pwa::components.push-prompt-teaser', 'pwa::push_prompt_teaser');
    }
}
