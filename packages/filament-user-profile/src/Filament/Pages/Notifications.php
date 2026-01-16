<?php

namespace BeeGoodIT\FilamentUserProfile\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Facades\Auth;

class Notifications extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bell';

    protected string $view = 'filament-user-profile::pages.notifications';

    protected static ?string $title = 'Notifications';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?int $navigationSort = 4;

    public bool $pushSupported = false;

    public bool $pushEnabled = false;

    public ?string $vapidPublicKey = null;

    /**
     * Check if the page should be accessible.
     * Only show if laravel-pwa push service is available.
     */
    public static function canAccess(): bool
    {
        return class_exists(\BeeGoodIT\LaravelPwa\Services\PushNotificationService::class);
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-user-profile::messages.Notifications');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'notifications';
    }

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null): string
    {
        // Use the me panel (no tenant)
        $panel ??= 'me';

        $panelInstance = Filament::getPanel($panel);

        if ($panelInstance) {
            // Construct URL using panel path and page slug
            $url = $panelInstance->getPath() . '/' . static::getSlug($panelInstance);

            if ($isAbsolute) {
                return url($url);
            }

            return '/' . ltrim($url, '/');
        }

        // Fallback to parent method
        return parent::getUrl($parameters, $isAbsolute, $panel, null);
    }

    public function getHeading(): string
    {
        return __('filament-user-profile::messages.Notifications');
    }

    public function getSubheading(): ?string
    {
        return __('filament-user-profile::messages.Manage your notification preferences');
    }

    public function mount(): void
    {
        // Check if push service is available and configured
        if (class_exists(\BeeGoodIT\LaravelPwa\Services\PushNotificationService::class)) {
            $pushService = resolve(\BeeGoodIT\LaravelPwa\Services\PushNotificationService::class);
            $this->pushSupported = true;
            $this->pushEnabled = $pushService->isEnabled();
            $this->vapidPublicKey = $pushService->getVapidPublicKey();
        }
    }

    /**
     * Check if user has push subscriptions.
     */
    public function hasPushSubscriptions(): bool
    {
        $user = Auth::user();

        if (!$user || !method_exists($user, 'hasPushSubscriptions')) {
            return false;
        }

        return $user->hasPushSubscriptions();
    }

    /**
     * Get subscription count for display.
     */
    public function getSubscriptionCount(): int
    {
        $user = Auth::user();

        if (!$user || !method_exists($user, 'pushSubscriptions')) {
            return 0;
        }

        return $user->pushSubscriptions()->count();
    }
}
