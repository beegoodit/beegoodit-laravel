# BeeGoodIT Filament Tenancy Plugins

Code catalog and morph assignments for Filament tenant plugin entitlements.

A **row** in `beegoodit_filament_tenancy_plugins_assignments` means the plugin is assigned. This package does **not** unregister Filament plugins. Licenses, when they exist, should **only** call this assign/revoke API.

## Installation

```bash
composer require beegoodit/filament-tenancy-plugins
```

Publish the config if you need to set the tenant model or slug attribute:

```bash
php artisan vendor:publish --tag=filament-tenancy-plugins-config
```

Run migrations:

```bash
php artisan migrate
```

## Setup

### 1. Add `HasPlugins` to the tenant model

```php
use BeegoodIT\FilamentTenancyPlugins\Models\Concerns\HasPlugins;

class Team extends Model
{
    use HasPlugins;
}
```

### 2. Point config at the tenant model

```php
// config/filament-tenancy-plugins.php

return [
    'tenant_model' => \App\Models\Team::class,
    'slug_attribute' => 'slug',
];
```

`tenant_model` is required for `tenancy-plugins:assign` / `tenancy-plugins:revoke`, and for showing slugs in `tenancy-plugins:list`.

### 3. Register plugin IDs from a service provider

Register catalog IDs from a **package or host** service provider. Do not import `filament()->getPlugins()`.

```php
use BeegoodIT\FilamentTenancyPlugins\Tenant;

public function boot(): void
{
    Tenant::register('billing', 'Billing');
    Tenant::register('sso', 'SSO', ['group' => 'auth']);
}
```

`$meta` is stored on the definition and unused by this package. Registering the same id and label again is a no-op (first definition wins, including meta). The same id with a different label throws.

## Usage

```php
$tenant->hasPlugin('billing');
$tenant->assignPlugin('billing'); // true when a row is created; false if already assigned
$tenant->revokePlugin('billing'); // true when a row is deleted; false if not assigned
$tenant->pluginIds();

Tenant::allows($tenant, 'billing'); // false if $tenant is null or not assigned
Tenant::for($tenant)->has('billing');
Tenant::has('billing'); // current Filament tenant, or false if none
Tenant::find('acme'); // configured tenant model, slug or key; unknown slug is null
Tenant::ensure($tenant, 'billing'); // throws NotAssignedException
```

Verticals that must not Composer-require this package should `class_exists` then call `allows()` / `ensure()`. Do not unregister Filament plugins when `allows()` is false.

Assign fires `PluginAssigned` only when a row is created. Revoke fires `PluginRevoked` only when a row is deleted.

## Artisan

```bash
php artisan tenancy-plugins:list
php artisan tenancy-plugins:assign billing --tenant=acme
php artisan tenancy-plugins:revoke billing --tenant=acme
```

`--tenant` accepts the configured slug attribute or the tenant key. Unknown plugin or unknown tenant fails. Already assigned / not assigned succeeds with a clear message.

`tenancy-plugins:list` works without `tenant_model` (morph class + key). When `tenant_model` is set and the morph type matches, slugs are resolved too.

## License

MIT License. See [LICENSE](LICENSE) for details.
