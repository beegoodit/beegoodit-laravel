<?php

namespace BeegoodIT\LaravelPwa\Filament;

use BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource;
use BeegoodIT\LaravelPwa\Filament\Resources\MessageResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class LaravelPwaPlugin implements Plugin
{
    public function getId(): string
    {
        return 'laravel-pwa';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                BroadcastResource::class,
                MessageResource::class,
                \BeegoodIT\LaravelPwa\Filament\Resources\PushSubscriptionResource::class,
            ])
            ->pages([
                \BeegoodIT\LaravelPwa\Filament\Pages\ManageNotificationSettings::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        if (config('pwa.navigation.register_filament_styles', true)) {
            \Filament\Support\Facades\FilamentView::registerRenderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => view('laravel-pwa::partials.pwa-styles')->render(),
            );
        }
    }

    public static function make(): static
    {
        return resolve(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament()->getPlugin('laravel-pwa');

        return $plugin;
    }
}
