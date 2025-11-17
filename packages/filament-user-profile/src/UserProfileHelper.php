<?php

namespace BeeGoodIT\FilamentUserProfile;

use BeeGoodIT\FilamentUserProfile\Filament\Pages\Appearance;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\Password;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\Profile;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\TwoFactor;
use Filament\Facades\Filament;
use Filament\Navigation\MenuItem;
use Laravel\Fortify\Features;

class UserProfileHelper
{
    /**
     * Get user menu items for all profile pages.
     *
     * @return array<string, MenuItem>
     */
    public function getUserMenuItems(): array
    {
        $items = [
            'profile' => MenuItem::make()
                ->label(__('Profile'))
                ->icon('heroicon-o-user-circle')
                ->url(function () {
                    $panel = Filament::getCurrentPanel();
                    $panelId = $panel?->getId() ?? 'admin';
                    $tenant = Filament::getTenant();
                    // Profile uses a different route name pattern (filament.portal.profile instead of filament.portal.pages.profile)
                    return route("filament.{$panelId}.profile", ['tenant' => $tenant?->id]);
                })
                ->sort(0),

            'password' => MenuItem::make()
                ->label(__('Password'))
                ->icon('heroicon-o-key')
                ->url(function () {
                    $tenant = Filament::getTenant();
                    return Password::getUrl(['tenant' => $tenant?->id]);
                })
                ->sort(1),

            'appearance' => MenuItem::make()
                ->label(__('Appearance'))
                ->icon('heroicon-o-paint-brush')
                ->url(function () {
                    $tenant = Filament::getTenant();
                    return Appearance::getUrl(['tenant' => $tenant?->id]);
                })
                ->sort(2),
        ];

        // Add two-factor menu item only if Fortify 2FA is enabled
        if (Features::canManageTwoFactorAuthentication()) {
            $items['two-factor'] = MenuItem::make()
                ->label(__('Two-Factor Authentication'))
                ->icon('heroicon-o-shield-check')
                ->url(function () {
                    $tenant = Filament::getTenant();
                    return TwoFactor::getUrl(['tenant' => $tenant?->id]);
                })
                ->sort(3);
        }

        return $items;
    }
}

