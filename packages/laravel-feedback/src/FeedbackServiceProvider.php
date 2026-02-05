<?php

namespace BeegoodIT\LaravelFeedback;

use BeegoodIT\LaravelFeedback\Livewire\FeedbackButton;
use BeegoodIT\LaravelFeedback\Livewire\FeedbackModal;
use BeegoodIT\LaravelFeedback\Models\FeedbackItem;
use BeegoodIT\LaravelFeedback\Policies\FeedbackItemPolicy;
use Filament\Facades\Filament;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FeedbackServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(__DIR__.'/config/feedback.php')) {
            $this->mergeConfigFrom(
                __DIR__.'/config/feedback.php',
                'feedback'
            );
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/config/feedback.php' => config_path('feedback.php'),
        ], 'feedback-config');

        // Publish migrations
        if (is_dir(__DIR__.'/../database/migrations')) {
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'feedback-migrations');
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish views
        if (is_dir(__DIR__.'/../resources/views')) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-feedback'),
            ], 'feedback-views');

            // Load views
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-feedback');
        }

        // Publish translations
        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/laravel-feedback'),
        ], 'feedback-lang');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'feedback');

        // Register Livewire components
        $this->registerLivewireComponents();

        // Register policies
        $this->registerPolicies();

        // Register Filament resources and menu items
        $this->registerFilamentResources();
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        if (class_exists(FeedbackModal::class)) {
            Livewire::component('laravel-feedback::feedback-modal', FeedbackModal::class);
        }
        if (class_exists(FeedbackButton::class)) {
            Livewire::component('laravel-feedback::feedback-button', FeedbackButton::class);
        }
    }

    /**
     * Register policies.
     */
    protected function registerPolicies(): void
    {
        if (class_exists(FeedbackItem::class) && class_exists(FeedbackItemPolicy::class)) {
            Gate::policy(
                FeedbackItem::class,
                FeedbackItemPolicy::class
            );
        }
    }

    /**
     * Register Filament resources and menu items.
     *
     * Note: Due to Filament's panel configuration system, resources and menu items
     * should be registered in panel providers. This method attempts automatic
     * registration, but manual registration in panel providers is recommended.
     */
    protected function registerFilamentResources(): void
    {
        // Add feedback button to all panels (including /me panel)
        // Use booted callback to ensure all panels are registered
        $this->app->booted(function (): void {
            // Register hook for all panels when Filament is serving
            Filament::serving(function (): void {
                foreach (Filament::getPanels() as $panel) {
                    $panel->renderHook(
                        PanelsRenderHook::USER_MENU_BEFORE,
                        fn (): string => Blade::render('@livewire("laravel-feedback::feedback-button")')
                    );
                }
            });
        });
    }
}
