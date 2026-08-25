# beegoodit/laravel-public-resources

Public resource URL primitives for BeeGoodIT Laravel platforms: Crockford `public_id`, `{slug}-{publicId}` keys, and path helpers for the **locale + mount + type** grammar.

## Constitution (org law)

### Path shape

```text
/{locale}/{mount}/{type}                         collection (e.g. events index)
/{locale}/{mount}/{type}/{slug}-{publicId}       member
/{locale}/{mount}/{type}/{publicId}/{action}     member actions (Rails-style)
/{locale}/{mount}/{parentType}/{parentKey}/{childType}   shallow scoped L/C only
```

| Layer | Localize? | Role |
|-------|-----------|------|
| **locale** | Yes | `/de`, `/en`, … |
| **mount** | Yes (i18n maps) | Plugin engine prefix (`programm` / `program`, …) |
| **type** | Yes | Resource under mount (`veranstaltungen` / `events`, `kategorien` / `categories`) |
| **slug** | Yes | Decoration on the member key |
| **publicId** | **No** | 8-char Crockford; stable across languages |

**No primary `path: ""`.** Every resource has an explicit `{type}` segment under the mount.

### Member keys

- Format: `{slug}-{publicId}` (or `{publicId}` alone)
- **Lookup by `publicId` only** — ignore slug
- Wrong / outdated slug → **200 OK** (optional soft 301 later; prefer `rel=canonical`)
- `public_id`: 8-char lowercase Crockford; unique **per site** when the model scopes uniqueness with `site_id`

### Surfaces

| Surface | Paths |
|---------|--------|
| **Public** | Grammar above |
| **Admin** | Filament defaults (`/admin/.../create`, `/{record}/edit`) — not this grammar |
| **API** | Separate mount; English type names; uuid / `public_id` |

### Non-goals

- Vanity marketing aliases as platform law
- Filament admin path rewrite
- Central “type registry” product
- Soft-301 middleware in this package (apps may add later)
- Host route registration / CMS catch-all (apps declare mount exclusions)

### Example (Eveant)

```text
/de/programm/veranstaltungen
/de/programm/veranstaltungen/summer-night-h3k7m2p9
/de/programm/kategorien/konzerte-k9mnpqrs

/en/program/events/summer-night-h3k7m2p9
/en/program/categories/concerts-k9mnpqrs
```

Apps own mount/type i18n maps; this package only formats/parses keys and ids.

## Installation

```bash
composer require beegoodit/laravel-public-resources
```

Path repository (monorepo / local):

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../../../composer/beegoodit-laravel/packages/*"
    }
  ],
  "require": {
    "beegoodit/laravel-public-resources": "@dev"
  }
}
```

## Usage

### Generate / validate

```php
use BeegoodIT\LaravelPublicResources\PublicId;

PublicId::generate();           // e.g. "h3k7m2p9"
PublicId::isValid('h3k7m2p9');  // true
PublicId::normalize('H3K7M2P9');
```

### Parse / format member keys

```php
use BeegoodIT\LaravelPublicResources\PublicResourceKey;

$key = PublicResourceKey::parse('summer-night-h3k7m2p9');
// $key->publicId === 'h3k7m2p9'
// $key->slug === 'summer-night'

PublicResourceKey::format('summer-night', 'h3k7m2p9');
// "summer-night-h3k7m2p9"
```

### Eloquent

```php
use BeegoodIT\LaravelPublicResources\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasPublicId;

    /** @return list<string> */
    public function publicIdUniquenessColumns(): array
    {
        return ['site_id'];
    }
}
```

Migration: `$table->string('public_id', 8);` plus unique index on `(site_id, public_id)` (or `public_id` alone if global).

### Paths

```php
use BeegoodIT\LaravelPublicResources\PublicResourcePath;

PublicResourcePath::collection('de', 'programm', 'veranstaltungen');
// /de/programm/veranstaltungen

PublicResourcePath::member('de', 'programm', 'veranstaltungen', 'summer-night', 'h3k7m2p9');
// /de/programm/veranstaltungen/summer-night-h3k7m2p9

PublicResourcePath::action('de', 'programm', 'veranstaltungen', 'h3k7m2p9', 'ics');
// /de/programm/veranstaltungen/h3k7m2p9/ics
```

## Testing

```bash
cd packages/laravel-public-resources
composer install   # if developing the package alone
# From monorepo root:
composer test -- --filter=LaravelPublicResources
# or:
./vendor/bin/phpunit packages/laravel-public-resources/tests
```

## License

MIT
