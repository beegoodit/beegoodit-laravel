# BeeGoodIT Filament I18n

User internationalization preferences for Filament applications: locale, timezone, and time format (12/24h).

## Installation

```bash
composer require beegoodit/filament-i18n
```

> [!TIP]
> **Monorepo / Local Path Repositories**:
> If you are installing this as a dependency of another package (like `filament-user-profile`) in a monorepo setup, you may need to explicitly add it to your root `composer.json` to ensure the service provider is properly discovered.

## Setup

### 1. Publish Migrations

```bash
php artisan vendor:publish --tag=filament-i18n-migrations
php artisan migrate
```

This adds to the `users` table:
- `locale` (varchar, default 'en')
- `timezone` (varchar, default 'UTC')
- `time_format` (varchar, default '24h')

### 2. Add Trait to User Model

```php
use BeeGoodIT\FilamentI18n\Models\Concerns\HasI18nPreferences;

class User extends Authenticatable
{
    use HasI18nPreferences;
    
    protected $fillable = [
        // ...
        'locale',
        'timezone',
        'time_format',
    ];
}
```

### 3. Register Middleware

Add to your Filament panel provider:

```php
use BeeGoodIT\FilamentI18n\Middleware\SetLocale;

public function panel(Panel $panel): Panel
{
    return $panel
        ->middleware([
            // ... other middleware
            SetLocale::class,
        ]);
}
```

## Usage

### User Methods

```php
// Get preferences
$user->getLocale();        // 'en' or 'de'
$user->getTimezone();      // 'Europe/Berlin'
$user->getTimeFormat();    // '12h' or '24h'

// Format time
$user->formatTime(now());  // '15:45' or '3:45 PM'
$user->formatDateTime(now()); // '2025-10-30 15:45' or '2025-10-30 3:45 PM'

// Check format
$user->prefers12HourFormat(); // true/false
```

### Filament Form Components

Build your profile settings form with dynamic locale options:

```php
use BeeGoodIT\FilamentI18n\Facades\FilamentI18n;
use Filament\Forms\Components\Radio;

Form::make()->schema([
    Radio::make('locale')
        ->label(__('filament-i18n::messages.locale'))
        ->options(FilamentI18n::localeOptions()) // Returns ['en' => 'English', ...]
        ->inline(),
    
    // ... timezone and time_format fields
])
```

### Available Locales Configuration

The package provides centralized locale configuration:

```php
// config/filament-i18n.php
return [
    'available_locales' => ['en', 'de', 'es'], // or via APP_AVAILABLE_LOCALES env var
    
    'locales' => [
        'en' => ['native' => 'English', 'flag' => '🇬🇧', 'rtl' => false],
        'de' => ['native' => 'Deutsch', 'flag' => '🇩🇪', 'rtl' => false],
        'es' => ['native' => 'Español', 'flag' => '🇪🇸', 'rtl' => false],
        // ... more locales
    ],
];
```

Publish to customize:

```bash
php artisan vendor:publish --tag=filament-i18n-config
```

### FilamentI18n Facade

```php
use BeeGoodIT\FilamentI18n\Facades\FilamentI18n;

// Get available locales
FilamentI18n::availableLocales();       // ['en', 'de', 'es']

// Get options for form select/radio (native names)
FilamentI18n::localeOptions();          // ['en' => 'English', 'de' => 'Deutsch', ...]

// Get options with flag emojis
FilamentI18n::localeOptionsWithFlags(); // ['en' => '🇬🇧 English', ...]

// Get full metadata for a locale
FilamentI18n::localeMetadata('de');     // ['native' => 'Deutsch', 'flag' => '🇩🇪', 'rtl' => false]

// Validation and utilities
FilamentI18n::isValidLocale('de');      // true
FilamentI18n::isRtl('ar');              // true
FilamentI18n::nativeName('de');         // 'Deutsch'
FilamentI18n::flag('de');               // '🇩🇪'
```

**Note**: In route files, use `config('filament-i18n.available_locales')` directly since routes load before service providers boot.

## Middleware Behavior

The `SetLocale` middleware automatically:
1. Checks if user is authenticated
2. Reads user's locale preference
3. Sets app locale: `App::setLocale($user->getLocale())`
4. All subsequent translations use user's preferred language

## Translation

Publish and customize translations:

```bash
php artisan vendor:publish --tag=filament-i18n-lang
```

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Filament 4.0+

## License

MIT License. See [LICENSE](LICENSE) for details.

