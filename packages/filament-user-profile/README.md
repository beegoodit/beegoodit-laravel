# BeeGoodIT Filament User Profile

User profile settings pages for Filament applications. Provides ready-to-use pages for profile management, password changes, appearance settings, and two-factor authentication.

## Installation

```bash
composer require beegoodit/filament-user-profile
```

## Setup

### 1. Configure Tailwind CSS (Required)

If you're using Tailwind CSS v4, you need to ensure Tailwind can scan the package's Blade templates for classes. The setup depends on whether you're using Filament panels.

#### Option A: Using Filament Panels (Recommended)

If your package views are rendered through Filament panels, you need to:

1. **Create or update your Filament theme CSS file** (e.g., `resources/css/filament/portal/theme.css`):

```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';
@source '../../../../vendor/beegoodit/filament-user-profile/resources/views/**/*.blade.php';
```

**Note**: If you're using a local path repository (monorepo), you must also add the path to the original package directory:
```css
/* Path from resources/css/filament/X/theme.css to monorepo package views */
@source '../../../../../../../composer/beegoodit-laravel/packages/filament-user-profile/resources/views/**/*.blade.php';
```
> [!TIP]
> Use a wildcard to cover all beegoodit packages if you have many:
> `@source '../../../../../../../composer/beegoodit-laravel/packages/*/resources/views/**/*.blade.php';`

2. **Register the theme in your panel provider**:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->viteTheme('resources/css/filament/portal/theme.css') // ← Must include this
        ->pages([
            // ... your pages
        ]);
}
```

3. **Include the theme in Vite** (`vite.config.js`):

```javascript
input: [
    'resources/css/app.css',
    'resources/js/app.js',
    'resources/css/filament/portal/theme.css', // ← Must be here
],
```

#### Option B: Not Using Filament Panels

If you're not using Filament panels, add the `@source` directive to your main CSS file:

```css
@import 'tailwindcss';

@source '../../vendor/beegoodit/filament-user-profile/resources/views/**/*.blade.php';
```

#### After Configuration

Rebuild your CSS assets:
```bash
npm run build
# or for development:
npm run dev
```

**Important**: If Tailwind classes don't work in your package views, check:
- ✅ The panel provider uses `->viteTheme()` pointing to your theme CSS
- ✅ The theme CSS file includes `@source` directives for the package views
- ✅ The theme CSS is included in Vite's `input` array
- ✅ CSS assets have been rebuilt after changes

### 2. Publish Timezone Data (Optional but Recommended)

For the interactive timezone map to work, publish the GeoJSON data:

```bash
php artisan vendor:publish --tag=filament-user-profile-timezone-data
```

### 3. Publish Two-Factor Authentication Migration (Required for 2FA)

If you want to use the Two-Factor Authentication page, you need to publish and run the migration:

```bash
php artisan vendor:publish --tag=filament-user-profile-migrations
php artisan migrate
```

**Note**: The Two-Factor Authentication page will only appear in navigation if:
- Laravel Fortify's two-factor authentication feature is enabled
- The required database columns exist (`two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`)

If the columns are missing, a warning will be logged and the page will be hidden from navigation.

### 4. Translations (Optional)

The package includes translations for English and German. Translations are automatically loaded and available without any setup.

If you want to customize translations, you can publish them:

```bash
php artisan vendor:publish --tag=filament-user-profile-lang
```

This will copy the translation files to `lang/vendor/filament-user-profile/` where you can customize them.

**Note**: The package uses namespaced translations (`filament-user-profile::messages.*`) for package-specific strings. Generic keys like `Save` use the standard `__('Save')` function, allowing you to override them in your application's translation files.

### 5. Locale Middleware

The settings panel (`/settings`) automatically includes locale middleware to apply the user's language preference. The package will:

1. First try to use your application's `App\Http\Middleware\SetLocale` middleware (if it exists)
2. Fall back to `BeeGoodIT\FilamentI18n\Middleware\SetLocale` from the `beegoodit/filament-i18n` package

This ensures the settings pages are displayed in the user's selected language.

### 6. Register Pages in Your Panel Provider

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

### 7. URLs

Pages will be automatically available at:
- `/portal/profile` (or your panel path + `/profile`)
- `/portal/password`
- `/portal/appearance`
- `/portal/two-factor`

**Note**: These pages are user-specific (not tenant-specific) and are accessible outside the tenant context, similar to the logout route. They use `isTenanted(): false` to ensure they're not scoped to a tenant.

The URLs follow your panel's configured path automatically.


## Unified Login

This package provides a `UnifiedAuthenticate` middleware to redirect unauthenticated users from any panel (e.g., Admin, Player) to the central `/me/login` page provided by this package.

### Usage

1. **Disable Native Login** on your other panels.
2. **Register Middleware** in your other panel providers.

```php
use BeeGoodIT\FilamentUserProfile\Http\Middleware\UnifiedAuthenticate;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        // Remove ->login() or ->loginRoute()
        ->authMiddleware([
            UnifiedAuthenticate::class,
             // ... other middleware
        ]);
}
```

This will ensure that all unauthenticated access to your panel redirects to the User Profile login page.

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

## Troubleshooting

### Timezone Map not visible
1. **Screen Width**: The map is hidden on small screens (`md:block`). Ensure your browser window is at least 768px wide.
2. **Tailwind Sources**: Check your compiled CSS. If `md:block` exists but the map is still hidden, ensure the `@source` paths in your theme CSS correctly point to the package's `.blade.php` files.
3. **GeoJSON Data**: Open the browser console (F12). If you see `Failed to load resource: timezones-tiny.geojson`, ensure you have published the data (`php artisan vendor:publish --tag=filament-user-profile-timezone-data`).
4. **JavaScript Errors**: If you see `L is not defined`, Leaflet.js failed to load from the CDN.

### Page not found or translations missing
If you're using a monorepo with path repositories, sub-dependencies like `beegoodit/filament-i18n` might not be automatically discovered.
**Fix**: Explicitly add the sub-dependency to your root `composer.json`:
```json
"require": {
    "beegoodit/filament-user-profile": "@dev",
    "beegoodit/filament-i18n": "@dev"
}
```

## License

MIT License. See [LICENSE](LICENSE) for details.

