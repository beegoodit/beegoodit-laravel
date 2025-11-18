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
     * Get user menu items for the main panel.
     * Only Profile is shown here - other items (Password, Appearance, 2FA) are in the settings panel navigation.
     *
     * @return array<string, MenuItem>
     */
    public function getUserMenuItems(): array
    {
        return [
            'profile' => MenuItem::make()
                ->label(__('Profile'))
                ->icon('heroicon-o-user-circle')
                ->url(fn () => Profile::getUrl())
                ->sort(0),
        ];
    }
}

