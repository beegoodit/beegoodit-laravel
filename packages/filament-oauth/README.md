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

**Note for OAuth users**: The package includes a migration to make the `password` field nullable in the `users` table. This is required for OAuth-only users who don't have passwords. The migration will be published automatically when you run `php artisan vendor:publish --tag=filament-oauth-migrations`.

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

**Step 1: Add environment variables to `.env`:**
```env
MICROSOFT_CLIENT_ID=your_client_id
MICROSOFT_CLIENT_SECRET=your_secret
MICROSOFT_TENANT_ID=your_tenant_id
OAUTH_MICROSOFT_ENABLED=true
OAUTH_AUTO_ASSIGN_TEAMS=true
```

**Step 2: Configure `config/services.php` (REQUIRED)**

⚠️ **Important**: Laravel Socialite requires the Microsoft provider to be configured in `config/services.php`. 

The package **automatically configures** this at runtime, but it's **recommended** to add it manually to `config/services.php` for clarity and to ensure it works with config caching:

Add to `config/services.php`:
```php
'microsoft' => [
    'client_id' => env('MICROSOFT_CLIENT_ID'),
    'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
    'redirect' => env('APP_URL') . '/portal/oauth/callback/microsoft',
    'tenant' => env('MICROSOFT_TENANT_ID', 'common'),
],
```

**Note:** 
- The package automatically merges this configuration on boot if not already present
- The package's `config/filament-oauth.php` is for package-specific features (team assignment, etc.)
- `config/services.php` is required by Laravel Socialite for OAuth credentials
- If you manually configure it in `config/services.php`, those values will take precedence

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

### Artisan Commands

#### Create Team with User

The package includes an artisan command to create teams and optionally attach users:

```bash
php artisan team:create
```

**Interactive Mode:**
```bash
php artisan team:create
# Prompts for: team name, user email, user name, password
```

**Non-Interactive Mode:**
```bash
# Create team with new user
php artisan team:create --name="Acme Corp" --user-email="admin@acme.com" --user-name="Admin User" --user-password="secret"

# Create team and attach existing user
php artisan team:create --name="Acme Corp" --user-email="admin@acme.com"

# Create team with OAuth configuration
php artisan team:create --name="Acme Corp" --oauth-provider="microsoft" --oauth-tenant-id="tenant-123"

# Create team with branding
php artisan team:create --name="Acme Corp" --primary-color="#ff0000" --secondary-color="#00ff00"

# Create team without user
php artisan team:create --name="Acme Corp" --no-user
```

**Options:**
- `--name` - Team name (required if non-interactive)
- `--slug` - Team slug (auto-generated if not provided)
- `--user-email` - Email of user to attach (creates new user if not exists)
- `--user-name` - Name of user (only used when creating new user)
- `--user-password` - Password for new user (auto-generated if not provided)
- `--oauth-provider` - OAuth provider (e.g., microsoft)
- `--oauth-tenant-id` - OAuth tenant ID
- `--primary-color` - Primary branding color (hex)
- `--secondary-color` - Secondary branding color (hex)
- `--no-user` - Create team without attaching a user

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

### "Provider 'microsoft' is not configured"

This error occurs when the Microsoft provider is not configured in `config/services.php`. 

**Solution:** Add the Microsoft configuration to `config/services.php`:

```php
'microsoft' => [
    'client_id' => env('MICROSOFT_CLIENT_ID'),
    'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
    'redirect' => env('APP_URL') . '/portal/oauth/callback/microsoft',
    'tenant' => env('MICROSOFT_TENANT_ID', 'common'),
],
```

Make sure:
1. The configuration exists in `config/services.php`
2. Your `.env` file has `MICROSOFT_CLIENT_ID` and `MICROSOFT_CLIENT_SECRET` set
3. Run `php artisan config:clear` after making changes

### "null value in column 'password' violates not-null constraint"

This error occurs when trying to create an OAuth user but the `password` column in the `users` table is not nullable.

**Solution:** Make sure you've published and run all migrations:

```bash
php artisan vendor:publish --tag=filament-oauth-migrations
php artisan migrate
```

The package includes a migration (`make_password_nullable_in_users_table.php`) that makes the password field nullable, which is required for OAuth-only users who don't have passwords.

## License

MIT License. See [LICENSE](LICENSE) for details.

