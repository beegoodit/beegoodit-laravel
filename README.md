# BeeGoodIT Laravel Packages

A monorepo containing reusable Laravel packages for BeeGoodIT applications.

## Packages

- **[beegoodit/eloquent-userstamps](packages/eloquent-userstamps)** - Track who created/updated records
- **[beegoodit/laravel-file-storage](packages/laravel-file-storage)** - Unified S3/local file storage with automatic URL generation
- **[beegoodit/laravel-cookie-consent](packages/laravel-cookie-consent)** - GDPR-compliant cookie consent banner
- **[beegoodit/filament-i18n](packages/filament-i18n)** - User locale, timezone, and time format preferences
- **[beegoodit/filament-user-avatar](packages/filament-user-avatar)** - User avatar upload and display
- **[beegoodit/filament-oauth](packages/filament-oauth)** - OAuth2 authentication with team auto-assignment
- **[beegoodit/filament-team-branding](packages/filament-team-branding)** - Team logo and color customization
- **[beegoodit/laravel-pwa](packages/laravel-pwa)** - Progressive Web App support

## Installation in Apps

Add path repository to your app's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../../../composer/beegoodit-laravel/packages/*"
    }
  ],
  "require": {
    "beegoodit/filament-i18n": "@dev"
  }
}
```

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Format code
composer format

# Test with coverage
composer test:coverage
```

## Documented Patterns

Some features don't need packages - use these patterns:

### Force HTTPS in Production

Add to `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }
}
```

### Use UUIDs

Use Laravel's built-in trait:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable {
    use HasUuids;
}

// In migrations:
$table->uuid('id')->primary();
```

## License

All packages are open-sourced software licensed under the [MIT license](LICENSE).

