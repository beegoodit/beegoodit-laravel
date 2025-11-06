# BeeGoodIT Filament OAuth

OAuth2 authentication with automatic team assignment for Filament applications.

## Installation

```bash
composer require beegoodit/filament-oauth
```

**Dependencies**: Requires `beegoodit/filament-user-avatar` and `dutchcodingcompany/filament-socialite`.

## Setup

### 1. Publish Migrations

⚠️ **Important**: This package depends on filament-socialite. You must publish migrations from both packages:

```bash
# 1. Publish filament-socialite migrations (REQUIRED)
php artisan vendor:publish --tag=filament-socialite-migrations

# 2. Publish filament-oauth migrations
php artisan vendor:publish --tag=filament-oauth-migrations

# 3. Run migrations
php artisan migrate
```

**Note for UUID users**: If your User model uses UUIDs, you'll need to modify the published `socialite_users` migration to use `uuid` instead of `id`.

### 1b. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=filament-oauth-config
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
OAUTH_AUTO_ASSIGN_TEAMS=true
```

The package automatically configures Microsoft OAuth. You can customize settings in `config/filament-oauth.php` if needed.

**Note:** If you want to use `config/services.php` for OAuth configuration (legacy approach), add:
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
use BeeGoodIT\FilamentOAuth\FilamentSocialitePluginHelper;
use DutchCodingCompany\FilamentSocialite\Provider;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(
            FilamentSocialitePluginHelper::make()
                ->providers([
                    Provider::make('microsoft')
                        ->label('Sign in with Microsoft 365')
                        ->icon('heroicon-o-building-office')
                ])
                ->registration(true)  // Enable new user registration via OAuth
        );
}
```

**Using the Helper:**
- `FilamentSocialitePluginHelper::make()` - Automatically configures:
  - ✅ `SocialiteUser` model with UUID support
  - ✅ Email verification for OAuth users (OAuth providers verify emails)
  - ✅ Proper user creation callback

**Important Configuration Options:**

- `->registration(true)` - **Required** to allow new users to register via OAuth. Set to `false` if you only want existing users to connect OAuth accounts.
- `->stateless(false)` - Can be set per provider if needed (default is stateful).

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

The package automatically (when `OAUTH_AUTO_ASSIGN_TEAMS=true`):
1. Detects OAuth tenant ID from Microsoft login
2. Finds or creates a team with that tenant ID
3. Assigns the user to that team

To disable automatic team assignment, set in `.env`:
```env
OAUTH_AUTO_ASSIGN_TEAMS=false
```

You can then implement custom team assignment logic using the `Registered` and `SocialiteUserConnected` events from `dutchcodingcompany/filament-socialite`.

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Filament 4.0+
- beegoodit/filament-user-avatar

## Troubleshooting

### "Registration of a new user is not allowed"

Make sure you have `->registration(true)` in your Filament panel configuration:

```php
FilamentSocialitePlugin::make()
    ->registration(true)  // ← Add this
```

### "Driver [microsoft] not supported"

Run these commands to clear caches:

```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

### "No such table: socialite_users"

You need to publish and run migrations from both packages:

```bash
php artisan vendor:publish --tag=filament-socialite-migrations
php artisan vendor:publish --tag=filament-oauth-migrations
php artisan migrate
```

## License

MIT License. See [LICENSE](LICENSE) for details.

