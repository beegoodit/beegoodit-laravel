<?php

namespace BeeGoodIT\LaravelPwa;

use BeeGoodIT\LaravelPwa\Channels\WebPushChannel;
use BeeGoodIT\LaravelPwa\Console\GenerateVapidKeysCommand;
use BeeGoodIT\LaravelPwa\Services\PushNotificationService;
use Illuminate\Notifications\ChannelManager;
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

        // Publish JavaScript
        $this->publishes([
            __DIR__ . '/../resources/js/push-notifications.js' => public_path('js/push-notifications.js'),
        ], 'pwa-js');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateVapidKeysCommand::class,
            ]);
        }

        // Register routes
        $this->registerRoutes();

        // Register notification channel
        $this->registerNotificationChannel();
    }

    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/pwa.php', 'pwa');

        // Register push notification service
        $this->app->singleton(PushNotificationService::class, function ($app) {
            return new PushNotificationService;
        });
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'api',
            'middleware' => ['web'],
        ], function () {
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
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('webPush', function ($app) {
                return $app->make(WebPushChannel::class);
            });
        });
    }
}
