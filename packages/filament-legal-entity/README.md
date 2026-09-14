# Filament Legal Entity

Rechtsträger records (legal name, structured address, country profiles) plus Filament to edit them. Part of the BeeGood IT package ecosystem.

v1 is **store + helpers** (`addressLines()`, `profileValue()`). No public routes, no Impressum Blade, no cookie banner, no acceptance gate.

## Installation

```bash
composer require beegoodit/filament-legal-entity
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag=filament-legal-entity-migrations
php artisan migrate
```

Register the plugin on the panel (Resource **on** by default):

```php
use BeegoodIT\FilamentLegalEntity\FilamentLegalEntityPlugin;
use App\Models\Team;

$panel->plugin(
    FilamentLegalEntityPlugin::make()
        ->legalableModels([Team::class])
);
```

TallCMS / embed the form without the Resource:

```php
FilamentLegalEntityPlugin::make()
    ->legalableModels([Site::class])
    ->hasResource(false);

LegalEntityForm::schema(includeOwner: false);
```

Add the trait on models you own:

```php
use BeegoodIT\FilamentLegalEntity\Models\Concerns\HasLegalEntity;

class Team extends Model
{
    use HasLegalEntity;
}
```

Do not add the trait to models you cannot edit. Query `LegalEntity` by `owner` instead.

## Ownership

Nullable morph (`owner_type` / `owner_id` as **string**). Empty MorphToSelect = **platform** (`owner` null). One entity per owner; one platform row. Tenant panels hide the owner field and assign the current tenant.

## Country

Searchable ISO 3166-1 select. Empty until picked; required to save. `DE` uses the German extras in JSON `profile`. Any other country uses `generic` (no extras). `vat_id` is optional and not unique. Hard delete; no soft deletes.
