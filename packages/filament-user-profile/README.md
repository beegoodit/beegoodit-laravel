# BeeGoodIT Filament User Profile

User profile settings pages for Filament applications. Provides ready-to-use pages for profile management, password changes, appearance settings, and two-factor authentication.

## Installation

```bash
composer require beegoodit/filament-user-profile
```

## Setup

### 1. Publish Timezone Data (Optional but Recommended)

For the interactive timezone map to work, publish the GeoJSON data:

```bash
php artisan vendor:publish --tag=filament-user-profile-timezone-data
```

### 2. Register Pages in Your Panel Provider

Add the profile pages to your Filament panel:

```php
use BeeGoodIT\FilamentUserProfile\Filament\Pages\Profile;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\Password;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\Appearance;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\TwoFactor;
use BeeGoodIT\FilamentUserProfile\Facades\UserProfile;

public function panel(Panel $panel): Panel
{
    return $panel
        ->pages([
            Profile::class,
            Password::class,
            Appearance::class,
            TwoFactor::class,
        ])
        ->userMenuItems(
            UserProfile::getUserMenuItems()
        );
}
```

### 3. URLs

Pages will be automatically available at:
- `/portal/profile` (or your panel path + `/profile`)
- `/portal/password`
- `/portal/appearance`
- `/portal/two-factor`

**Note**: These pages are user-specific (not tenant-specific) and are accessible outside the tenant context, similar to the logout route. They use `isTenanted(): false` to ensure they're not scoped to a tenant.

The URLs follow your panel's configured path automatically.

## Features

### Phase 1 (Current)

- ✅ Profile page with heading
- ✅ Password page with heading
- ✅ Appearance page with heading
- ✅ Two-Factor Authentication page with heading
- ✅ User menu items helper
- ✅ Conditional Two-Factor menu item (only if Fortify 2FA enabled)

### Phase 4 (Current)

- ✅ Theme preference toggle (dark/light/system) - client-side only
- ✅ Localization preferences (locale, timezone, time format)
- ✅ Interactive timezone map with Leaflet.js
- ✅ Timezone picker component with mobile fallback

### Upcoming Phases

- **Phase 2**: Profile page functionality (name, email, avatar, i18n)
- **Phase 3**: Password change functionality
- **Phase 5**: Two-Factor Authentication functionality

## Customization

### Custom User Menu Items

You can customize the user menu items by manually registering them:

```php
use Filament\Navigation\MenuItem;

->userMenuItems([
    'profile' => MenuItem::make()
        ->label('My Profile')
        ->icon('heroicon-o-user')
        ->url(fn (): string => Profile::getUrl())
        ->sort(0),
    // ... other items
])
```

### Hide Navigation

To hide pages from the main navigation (they'll still be accessible via user menu):

```php
// In your page class
protected static bool $shouldRegisterNavigation = false;
```

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Filament 4.0+
- Laravel Fortify (for Two-Factor Authentication page)
- User model with `locale`, `timezone`, and `time_format` fields (see `beegoodit/filament-i18n` package)

## Timezone Map Data

The timezone picker includes an interactive map powered by Leaflet.js. The required GeoJSON file is included in the package and will be published automatically.

### Publish Timezone Data

```bash
php artisan vendor:publish --tag=filament-user-profile-timezone-data
```

This will copy the GeoJSON file to `public/data/timezones-tiny.geojson`.

**Note**: The timezone picker will fall back to a simple select dropdown on mobile devices or if the GeoJSON file is not available.

## License

MIT License. See [LICENSE](LICENSE) for details.

