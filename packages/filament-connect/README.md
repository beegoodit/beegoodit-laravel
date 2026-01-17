# BeeGoodIT Filament Connect

Multi-tenant API credential management for Filament applications. This package provides a centralized way to manage external service credentials (like API tokens) for different tenants (teams).

## Installation

```bash
composer require beegoodit/filament-connect
```

## Setup

### 1. Register Resource

Add the `ApiAccountResource` to your Filament panel provider:

```php
use Beegoodit\FilamentConnect\Filament\Resources\ApiAccountResource;

public function panel(Panel $panel): Panel
{
    return $panel
        ->resources([
            ApiAccountResource::class,
        ]);
}
```

### 2. Manual Setup (Optional)

You can publish the migration if you need to customize it:

```bash
php artisan vendor:publish --tag=filament-connect-migrations
php artisan migrate
```

## Usage

### Storing Credentials

Users can manage credentials through the "Connect" resource in their portal. Each account is linked to a service name (e.g., `tournament-io`) and stores credentials as JSON.

### Retrieving Credentials

Use the `Connect` facade to retrieve credentials for the current tenant:

```php
use Beegoodit\FilamentConnect\Facades\Connect;

$creds = Connect::getCredentials('tournament-io');
$token = $creds['api_token'] ?? null;
```

### Tenant Model Requirement

Your tenant model (e.g., `Team`) must be an Eloquent model. The package automatically filters credentials based on the current tenant in Filament.

## Features

- ✅ Multi-tenant aware (`owner_id`, `owner_type`)
- ✅ Encrypted/Secure storage pattern via JSON columns
- ✅ Filament resource for easy management
- ✅ Simple Facade for developers

## License

MIT License. See [LICENSE](LICENSE) for details.
