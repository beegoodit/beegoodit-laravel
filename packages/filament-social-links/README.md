# Filament Social Links

A reusable package for managing social media links across Filament applications. Supports polymorphic relationships, allowing any model (Teams, Tours, People) to have associated social profiles with dynamic URL generation.

## Features

- **Polymorphic Relationships**: Attach social links to any Eloquent model using the `HasSocialLinks` trait.
- **Platform Management**: Database-driven social platforms (Facebook, Instagram, TikTok, etc.) with seeded defaults.
- **Handle-Based URLs**: Users enter just the handle (e.g., `foosbeaver`), and the full URL is generated automatically.
- **Filament Admin Resources**: Full CRUD for `SocialPlatformResource` and `SocialLinkResource`.
- **RelationManager**: Easy drop-in tab for managing links on any parent resource.
- **FontAwesome Icons**: Each platform supports a FontAwesome Brand icon for consistent UI.
- **Multi-language Support**: Full translations for English, German, and Spanish.

## Installation

```bash
composer require beegoodit/filament-social-links
```

Run the migration:

```bash
php artisan migrate
```

Seed the default platforms:

```bash
php artisan db:seed --class="BeegoodIT\\FilamentSocialLinks\\Database\\Seeders\\SocialPlatformSeeder"
```

## Admin Panel Setup

Register the resources in your `AdminPanelProvider`:

```php
use BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialPlatformResource;
use BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialLinkResource;

public function panel(Panel $panel): Panel
{
    return $panel
        ->resources([
            SocialPlatformResource::class,
            SocialLinkResource::class,
        ])
        ->navigationGroups([
            // ... your groups
            __('filament-social-links::social.navigation_group'), // "Social Media"
            // ...
        ]);
}
```

After registering, clear the cache:

```bash
php artisan filament:cache-components
```

## Usage

### 1. Add the Trait to Your Model

```php
use BeegoodIT\FilamentSocialLinks\Models\Concerns\HasSocialLinks;

class Team extends Model
{
    use HasSocialLinks;
}
```

### 2. Add the Relation Manager to Your Resource

For inline management on parent resources (recommended for entities like Teams, Tours):

```php
use BeegoodIT\FilamentSocialLinks\Filament\RelationManagers\SocialLinksRelationManager;

class TeamResource extends Resource
{
    public static function getRelations(): array
    {
        return [
            SocialLinksRelationManager::class,
        ];
    }
}
```

### 3. Display in Blade Templates

```blade
@foreach($team->socialLinks as $link)
    <a href="{{ $link->url }}" title="{{ $link->platform->name }}">
        @svg($link->platform->icon, 'w-5 h-5')
    </a>
@endforeach
```

## Default Platforms

The seeder includes: Facebook, Instagram, TikTok, YouTube, Twitch, Telegram, X (Twitter), Discord, LinkedIn.

---

Created as part of the BeegoodIT Shared Package Ecosystem.
