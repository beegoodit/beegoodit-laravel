# BeeGoodIT Filament Tenancy Plugins

Code catalog and morph assignments for Filament tenant (and optional user) plugin entitlements.

A **row** in `beegoodit_filament_tenancy_plugins_assignments` means the plugin is assigned. This package does **not** unregister Filament plugins. Licenses, when they exist, should **only** call this assign/revoke API.

## Installation

```bash
composer require beegoodit/filament-tenancy-plugins
```

Publish the config if you need to set the tenant or user model:

```bash
php artisan vendor:publish --tag=filament-tenancy-plugins-config
```

Run migrations:

```bash
php artisan migrate
```

## Setup

### 1. Add `HasPlugins` to subject models

```php
use BeegoodIT\FilamentTenancyPlugins\Models\Concerns\HasPlugins;

class Team extends Model
{
    use HasPlugins;
}

class User extends Model
{
    use HasPlugins;
}
```

### 2. Point config at the models

```php
// config/filament-tenancy-plugins.php

return [
    'tenant_model' => \App\Models\Team::class,
    'slug_attribute' => 'slug',
    'user_model' => \App\Models\User::class,
    'user_lookup_attribute' => 'email',
];
```

`tenant_model` is required for `tenancy-plugins:assign` / `revoke` with `--tenant`, and for showing slugs in `tenancy-plugins:list`.  
`user_model` is required for `--user`, and for showing emails in `list`.

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

$user->assignPlugin('billing'); // same morph API on any HasPlugins model

Tenant::allows($tenant, 'billing'); // false if $tenant is null or not assigned
Tenant::for($tenant)->has('billing');
Tenant::has('billing'); // current Filament tenant, or false if none
Tenant::find('acme'); // configured tenant model, slug or key; unknown slug is null
Tenant::findUser('rva@example.test'); // configured user model, email (case-insensitive) or key
Tenant::ensure($tenant, 'billing'); // throws NotAssignedException
```

Verticals that must not Composer-require this package should `class_exists` then call `allows()` / `ensure()`. Do not unregister Filament plugins when `allows()` is false. Do **not** pass a User into `Tenant::allows` as a Filament tenant.

Assign fires `PluginAssigned` only when a row is created. Revoke fires `PluginRevoked` only when a row is deleted.

## Artisan

```bash
php artisan tenancy-plugins:list
php artisan tenancy-plugins:assign billing --tenant=acme
php artisan tenancy-plugins:revoke billing --tenant=acme
php artisan tenancy-plugins:assign billing --user=rva@example.test
php artisan tenancy-plugins:revoke billing --user=rva@example.test
```

Provide **exactly one** of `--tenant` or `--user`.  
`--tenant` accepts the configured slug attribute or the tenant key.  
`--user` accepts the configured lookup attribute (default `email`, case-insensitive) or the user key.  
Unknown plugin, unknown subject, or both/neither subject options: **fail**. Already assigned / not assigned: **success** + clear message.

`tenancy-plugins:list` works without models configured (morph class + key). When `tenant_model` / `user_model` are set and the morph type matches, slugs or emails are resolved too.

## License

MIT License. See [LICENSE](LICENSE) for details.
