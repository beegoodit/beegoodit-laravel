# filament-entry-links

Marketing / QR **entry links** for Laravel: public URLs under a configurable prefix (default `link/{token}-{slug}`) that redirect to a stored target URL, with optional scheduling, **Filament** admin CRUD, and configurable target URL allowlisting.

## Install

```bash
composer require beegoodit/filament-entry-links
```

The service provider is auto-discovered.

## Configuration

Publish or create `config/filament-entry-links.php`. Keys:

- `route_prefix` — path segment before `{token}-{slug}` (default: `link`).
- `middleware` — route middleware (include your domain / locale middleware if needed).
- `allowed_url_mode` — `off` | `same_app` | `allowlist`.
- `allowed_hosts` — used when mode is `allowlist`.
- `home_url` — optional URL for “home” buttons on static pages (default: `url('/')`).

## Filament

Register the resource on your admin panel:

```php
->resources([
    \BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource::class,
])
```

Navigation group uses `__('navigation.groups.marketing')` — define that key in your app `lang/*/navigation.php`.

Authorization uses `EntryLinkPolicy` (expects authenticated users with `is_admin`).

## Event

`BeegoodIT\FilamentEntryLinks\Events\EntryLinkFollowed` is fired after validation and before redirect.

## Views & translations

Publish tags: `filament-entry-links-views`, `filament-entry-links-lang`.

## Tests (monorepo)

From the BeegoodIT Laravel packages repository root (with `phpunit.xml.dist`):

```bash
./vendor/bin/phpunit packages/filament-entry-links/tests
```
