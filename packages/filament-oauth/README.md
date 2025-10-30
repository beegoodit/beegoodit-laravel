# BeeGoodIT Filament OAuth

OAuth2 authentication with automatic team assignment for Filament applications.

## Installation

```bash
composer require beegoodit/filament-oauth
```

**Dependencies**: Requires `beegoodit/filament-user-avatar`.

## Setup

### 1. Publish Migrations

```bash
php artisan vendor:publish --tag=oauth-migrations
php artisan migrate
```

### 2. Add Traits to Models

**User Model:**
```php
use BeeGoodIT\FilamentOAuth\Models\Concerns\HasOAuth;
use Filament\Models\Contracts\HasTenants;

class User extends Authenticatable implements HasTenants
{
    use HasOAuth;
}
```

### 3. Configure OAuth

Add to `.env`:
```env
MICROSOFT_CLIENT_ID=your_client_id
MICROSOFT_CLIENT_SECRET=your_secret
MICROSOFT_TENANT_ID=your_tenant_id
OAUTH_MICROSOFT_ENABLED=true
```

Add to `config/services.php`:
```php
'microsoft' => [
    'client_id' => env('MICROSOFT_CLIENT_ID'),
    'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
    'tenant_id' => env('MICROSOFT_TENANT_ID', 'common'),
    'redirect' => env('APP_URL') . '/portal/oauth/callback/microsoft',
],
```

### 4. Configure Filament Panel

```php
use BeeGoodIT\FilamentOAuth\Models\SocialiteUser;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(
            FilamentSocialitePlugin::make()
                ->socialiteUserModelClass(SocialiteUser::class)
                ->providers([
                    Provider::make('microsoft')
                        ->label('Sign in with Microsoft 365')
                        ->icon('heroicon-o-building-office')
                ])
        );
}
```

## Features

- ✅ OAuth2 authentication (Microsoft 365)
- ✅ Automatic team assignment based on tenant ID
- ✅ OAuth account storage (encrypted tokens)
- ✅ Avatar sync from OAuth provider
- ✅ Team creation if not exists
- ✅ Multi-provider support

## Usage

### Check OAuth Status

```php
// Check if user has OAuth account
$user->hasOAuthProvider('microsoft');

// Get OAuth account
$oauthAccount = $user->getOAuthAccount('microsoft');

// Check if user is OAuth-only (no password)
$user->isOAuthOnly();

// Check token expiration
$oauthAccount->isTokenExpired();
```

### Team Assignment

The package automatically:
1. Detects OAuth tenant ID from Microsoft login
2. Finds or creates a team with that tenant ID
3. Assigns the user to that team

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Filament 4.0+
- beegoodit/filament-user-avatar

## License

MIT License. See [LICENSE](LICENSE) for details.

