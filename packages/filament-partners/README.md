# Filament Partners

Partners and sponsors management for Filament with polymorphic scope and active period. Manage platform-, team-, or tour-scoped partners from a single Admin resource.

## Features

- **Polymorphic ownership**: Partners can belong to the platform (null), or to specific models (e.g. Team, Tour) via `partnerable_type` / `partnerable_id`.
- **Active period**: `active_from` / `active_to` with `scopeActive()` and optional `activeAt()` for visibility.
- **Types**: PartnerType enum (e.g. Partner, Sponsor) with optional labels.
- **Sortable**: Position-based ordering (Spatie Eloquent Sortable); reorderable in Filament table.
- **Filament resource**: Full CRUD (PartnerResource); register in Admin and/or tenant panels. In Admin, configure `partnerable_models` to allow selecting owner (Team, Tour, etc.); in tenant context the resource is scoped and partnerable is set from the tenant.
- **Translations**: EN/DE/ES lang keys under `filament-partners::partner.*`.

## Installation

```bash
composer require beegoodit/filament-partners
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag=filament-partners-migrations
php artisan migrate
```

Publish config (optional):

```bash
php artisan vendor:publish --tag=filament-partners-config
```

## Configuration

In `config/filament-partners.php` (or the published file):

- **partnerable_models**: Array of Eloquent model class names that can own partners. Used in Admin for the partnerable MorphToSelect (e.g. `[\App\Models\Team::class, \App\Models\Tour::class]`). Leave empty if you only use platform partners or tenant-scoped panels.
- **logo_disk**, **logo_directory**, **logo_max_size**: Logo upload settings.

## Register the resource

Register `PartnerResource` in your Filament Admin (and optionally in a team/tour panel with tenancy):

```php
use BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource;

// In AdminPanelProvider or similar
->resources([
    PartnerResource::class,
])
```

When using in a tenant panel, the resource uses `tenantOwnershipRelationshipName => 'partnerable'`; hide the partnerable field and set it from the tenant on create.

## Usage

### Add the trait to models that own partners

```php
use BeegoodIT\FilamentPartners\Models\Concerns\HasPartners;

class Team extends Model
{
    use HasPartners;
}
```

### Display active partners (e.g. in views)

```php
$partners = $team->partners()->active()->orderBy('position')->get();
// or for a tour: $tour->partners()->active()->orderBy('position')->get();
// or platform: Partner::platform()->active()->orderBy('position')->get();
```

Use `$partner->getLogoUrl()` for logo URLs and the package lang keys for labels.

---

Part of the BeegoodIT shared package ecosystem.
