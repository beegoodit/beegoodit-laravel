# BeeGoodIT Filament Team Branding

Team logo and color customization for Filament multi-tenant applications.

## Installation

```bash
composer require beegoodit/filament-team-branding
```

**Dependencies**: Requires `beegoodit/laravel-file-storage`.

## Setup

### 1. Publish Migration

```bash
php artisan vendor:publish --tag=team-branding-migrations
php artisan migrate
```

This adds to the `teams` table:
- `slug` (unique identifier)
- `logo` (file path)
- `primary_color` (hex color)
- `secondary_color` (hex color)
- `oauth_provider` (for team auto-assignment)
- `oauth_tenant_id` (for team auto-assignment)

### 2. Add Trait to Team Model

```php
use BeeGoodIT\FilamentTeamBranding\Models\Concerns\HasBranding;

class Team extends Model
{
    use HasBranding;
    
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'primary_color',
        'secondary_color',
        'oauth_provider',
        'oauth_tenant_id',
    ];
}
```

### 3. Configure Filament Panel

Use team logo and name dynamically:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->brandName(fn () => filament()->getTenant()?->name ?? 'Your App')
        ->brandLogo(fn () => filament()->getTenant()?->getFilamentLogoUrl() ?? asset('images/logo.svg'))
        ->brandLogoHeight('2rem');
}
```

## Usage

### Get Logo URL

```php
$team->getLogoUrl();          // Automatic S3 signed URL or public URL
$team->getFilamentLogoUrl();  // For Filament navbar
```

### Team Settings Page

Create a team profile page in your app:

```php
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;

Section::make('Branding')
    ->schema([
        FileUpload::make('logo')
            ->label('Team Logo')
            ->image()
            ->disk(config('filesystems.default'))
            ->directory(fn () => 'teams/logo/' . filament()->getTenant()->id)
            ->maxSize(2048),
        
        ColorPicker::make('primary_color')
            ->label('Primary Brand Color'),
        
        ColorPicker::make('secondary_color')
            ->label('Secondary Color'),
    ])
```

### Find Team by OAuth Tenant

```php
$team = Team::whereOAuthTenant('microsoft', $tenantId)->first();
```

## Features

- ✅ Team logo upload with S3 support
- ✅ Primary/secondary color customization
- ✅ OAuth tenant mapping (for auto-assignment)
- ✅ Automatic URL generation
- ✅ Filament integration

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Filament 4.0+
- beegoodit/laravel-file-storage

## License

MIT License. See [LICENSE](LICENSE) for details.

