# BeeGoodIT Laravel Packages

A monorepo containing reusable Laravel packages for BeeGoodIT applications.

## Packages

- **[beegoodit/eloquent-userstamps](packages/eloquent-userstamps)** - Track who created/updated records
- **[beegoodit/laravel-file-storage](packages/laravel-file-storage)** - Unified S3/local file storage with automatic URL generation
- **[beegoodit/laravel-cookie-consent](packages/laravel-cookie-consent)** - GDPR-compliant cookie consent banner
- **[beegoodit/filament-i18n](packages/filament-i18n)** - User locale, timezone, and time format preferences
- **[beegoodit/filament-user-avatar](packages/filament-user-avatar)** - User avatar upload and display
- **[beegoodit/filament-oauth](packages/filament-oauth)** - OAuth2 authentication with team auto-assignment
- **[beegoodit/filament-tenancy](packages/filament-tenancy)** - Multi-tenancy support with team branding and management
- **[beegoodit/filament-user-profile](packages/filament-user-profile)** - User profile settings pages
- **[beegoodit/laravel-pwa](packages/laravel-pwa)** - Progressive Web App support
- **[beegoodit/filament-tenancy-roles](packages/filament-tenancy-roles)** - Role-based authorization for Filament multi-tenancy
- **[beegoodit/filament-tenancy-domains](packages/filament-tenancy-domains)** - Polymorphic domain and subdomain management for Filament applications
- **[beegoodit/filament-connect](packages/filament-connect)** - Multi-tenant API credential management for Filament
- **[beegoodit/filament-social-links](packages/filament-social-links)** - Polymorphic social media link management for Filament
- **[beegoodit/filament-legal](packages/filament-legal)** - Centralized legal compliance for Filament applications
- **[beegoodit/filament-knowledge-base](packages/filament-knowledge-base)** - Standardized Knowledge Base for BeegoodIT Filament Apps
- **[beegoodit/laravel-feedback](packages/laravel-feedback)** - Feedback system for Laravel applications with Filament integration

## Guides

- **[Building an app with all packages](BUILDING-AN-APP.md)** – Step-by-step guide to get a new Laravel 12 / Filament 4 app up and running with every BeeGoodIT package and [PATTERNS.md](PATTERNS.md). Prerequisites: PHP 8.4+, Laravel 12+, Filament 4+.
- **Main features and default paths** when using all packages: see [Main features of the full app](BUILDING-AN-APP.md#main-features-of-the-full-app) in BUILDING-AN-APP.md.
- **Recommended route and panel layout** (portal, team, admin, landing): see [PATTERNS.md – Default routes and panels](PATTERNS.md#default-routes-and-panels) and [BUILDING-AN-APP.md](BUILDING-AN-APP.md).

## Installation in Apps

### Option 1: Packagist (Production)

Install packages directly from Packagist:

```bash
composer require beegoodit/filament-i18n
```

Or add to your `composer.json`:

```json
{
  "require": {
    "beegoodit/filament-i18n": "^1.0"
  }
}
```

### Option 2: Path Repository (Development)

For local development, use a path repository in your app's `composer.json`:

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

**Note**: Path repositories take precedence over Packagist, so this is perfect for local development and testing package changes.

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

### Customize Filament Logout Redirect

By default, Filament redirects users to the login page after logout. To redirect to the home page instead:

**1. Create a Custom LogoutResponse Class**

Create `app/Http/Responses/LogoutResponse.php`:

```php
<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        return redirect('/');
    }
}
```

**2. Bind the Custom LogoutResponse in AppServiceProvider**

Add to `app/Providers/AppServiceProvider.php`:

```php
use App\Http\Responses\LogoutResponse;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;

public function register(): void
{
    $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
}
```

This ensures users are redirected to the home page (`/`) after logging out from Filament panels, showing the landing page with "Dashboard" and "Get Started" buttons.

## License

All packages are open-sourced software licensed under the [MIT license](LICENSE).

