<?php

namespace BeegoodIT\LaravelPwa;

use BeegoodIT\LaravelPwa\Channels\WebPushChannel;
use BeegoodIT\LaravelPwa\Console\GenerateVapidKeysCommand;
use BeegoodIT\LaravelPwa\Services\PushNotificationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
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

        // Publish config
        $this->publishes([
            __DIR__.'/../config/pwa.php' => config_path('pwa.php'),
        ], 'pwa-config');

        // Publish migrations
        if (! $this->migrationExists('create_push_subscriptions_table')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_push_subscriptions_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_push_subscriptions_table.php'),
            ], 'pwa-migrations');
        }

        if (! $this->migrationExists('update_pwa_tables')) {
            $this->publishes([
                __DIR__.'/../database/migrations/update_pwa_tables.php.stub' => database_path('migrations/'.date('Y_m_d_His', time() + 1).'_update_pwa_tables.php'),
            ], 'pwa-migrations');
        }

        if (! $this->migrationExists('create_pwa_settings')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_pwa_settings_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time() + 2).'_create_pwa_settings.php'),
            ], 'pwa-migrations');
        }

        // Publish translations
        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/laravel-pwa'),
        ], 'pwa-lang');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-pwa');

        // Publish JavaScript
        $this->publishes([
            __DIR__.'/../resources/js/push-notifications.js' => public_path('js/push-notifications.js'),
        ], 'pwa-js');

        // Publish CSS
        $this->publishes([
            __DIR__.'/../resources/css/push-prompt.css' => public_path('css/push-prompt.css'),
        ], 'pwa-css');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateVapidKeysCommand::class,
                \BeegoodIT\LaravelPwa\Console\SendPushNotificationCommand::class,
                \BeegoodIT\LaravelPwa\Console\ReleaseOnHoldMessagesCommand::class,
                \BeegoodIT\LaravelPwa\Console\ToggleDeliverySystemCommand::class,
            ]);
        }

        // Register routes
        $this->registerRoutes();

        // Register notification channel
        $this->registerNotificationChannel();

        // Register Blade components & directives
        $this->registerBladeComponents();
        $this->registerBladeDirectives();

        // Register rate limiter for notifications
        RateLimiter::for('pwa-notifications', fn (object $job) => Limit::perMinute(config('pwa.notifications.rate_limit.pushes_per_minute', 50)));
    }

    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../config/pwa.php', 'pwa');

        // Register push notification service
        $this->app->singleton(PushNotificationService::class, fn ($app): \BeegoodIT\LaravelPwa\Services\PushNotificationService => new PushNotificationService);
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'api',
            'middleware' => config('pwa.push.middleware', ['web']),
        ], function (): void {
            Route::post('/push-subscriptions', [
                \BeegoodIT\LaravelPwa\Http\Controllers\PushSubscriptionController::class,
                'store',
            ])->name('push-subscriptions.store');

            Route::delete('/push-subscriptions', [
                \BeegoodIT\LaravelPwa\Http\Controllers\PushSubscriptionController::class,
                'destroy',
            ])->name('push-subscriptions.destroy');

            Route::get('/pwa/notifications/{message}/open', [
                \BeegoodIT\LaravelPwa\Http\Controllers\NotificationController::class,
                'trackOpen',
            ])->name('pwa.notifications.open');
        });
    }

    protected function registerNotificationChannel(): void
    {
        Notification::resolved(function (ChannelManager $service): void {
            $service->extend('webPush', fn ($app) => $app->make(WebPushChannel::class));
        });
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('pwaHead', fn (): string => "<?php echo view('laravel-pwa::partials.pwa-meta')->render(); ?>");

        Blade::directive('pwaScripts', fn (): string => "<?php echo \"<script src='\" . asset('js/push-notifications.js') . \"'></script>\"; ?>");

        Blade::directive('pwaStyles', fn (): string => "<?php echo \"<link rel='stylesheet' href='\" . asset('css/push-prompt.css') . \"'>\"; ?>");
    }

    protected function registerBladeComponents(): void
    {
        Blade::component('laravel-pwa::components.push-prompt-teaser', 'pwa::push_prompt_teaser');
    }

    protected function migrationExists(string|array $migrationNames): bool
    {
        $migrationNames = (array) $migrationNames;
        $path = database_path('migrations');
        $files = scandir($path);

        foreach ($files as $file) {
            foreach ($migrationNames as $name) {
                if (str_contains($file, (string) $name)) {
                    return true;
                }
            }
        }

        return false;
    }
}
