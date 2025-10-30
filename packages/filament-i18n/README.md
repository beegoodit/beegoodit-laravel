# BeeGoodIT Filament I18n

User internationalization preferences for Filament applications: locale, timezone, and time format (12/24h).

## Installation

```bash
composer require beegoodit/filament-i18n
```

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

Build your profile settings form:

```php
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;

Form::make()->schema([
    Radio::make('locale')
        ->label(__('filament-i18n::messages.locale'))
        ->options([
            'en' => 'English',
            'de' => 'Deutsch',
        ])
        ->inline(),
    
    Select::make('timezone')
        ->label(__('filament-i18n::messages.timezone'))
        ->options([
            'UTC' => 'UTC',
            'Europe/Berlin' => 'Europe/Berlin',
            'Europe/London' => 'Europe/London',
            'America/New_York' => 'America/New York',
            // ... more timezones
        ]),
    
    Radio::make('time_format')
        ->label(__('filament-i18n::messages.time_format'))
        ->options([
            '12h' => __('filament-i18n::messages.time_format_12h'),
            '24h' => __('filament-i18n::messages.time_format_24h'),
        ])
        ->inline(),
])
```

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

