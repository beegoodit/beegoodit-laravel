# Filament Social Links

A reusable package for managing social media links across Filament applications. Supports polymorphic relationships, allowing any model (Teams, Tours, People) to have associated social profiles with dynamic URL generation.

## Features

- **Polymorphic Relationships**: Attach social links to any Eloquent model using the `HasSocialLinks` trait.
- **Platform Management**: Database-driven social platforms (Facebook, Instagram, TikTok, etc.) with seeded defaults.
- **Handle-Based URLs**: Users enter just the handle (e.g., `foosbeaver`), and the full URL is generated automatically.
- **Filament Integration**: Includes `SocialLinksRelationManager` for easy drop-in tab management.
- **FontAwesome Icons**: Each platform supports a FontAwesome Brand icon for consistent UI.
- **Multi-language Support**: Full translations for English, German, and Spanish.
- **Userstamps & UUIDs**: All records are tracked with `created_by_id` and `updated_by_id`.

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
