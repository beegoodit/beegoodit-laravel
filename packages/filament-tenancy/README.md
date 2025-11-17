# BeeGoodIT Filament Tenancy

Multi-tenancy support for Filament applications with team branding, management pages, and dynamic theming.

## Installation

```bash
composer require beegoodit/filament-tenancy
```

**Dependencies**: Requires `beegoodit/laravel-file-storage`.

## Setup

### 1. Publish Migration

```bash
php artisan vendor:publish --tag=tenancy-migrations
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
use BeeGoodIT\FilamentTenancy\Models\Concerns\HasBranding;

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

### Team Registration and Profile Pages

The package provides ready-to-use pages for team registration and profile management:

```php
use BeeGoodIT\FilamentTenancy\Filament\Pages\RegisterTeam;
use BeeGoodIT\FilamentTenancy\Filament\Pages\EditTeamProfile;

public function panel(Panel $panel): Panel
{
    return $panel
        ->tenant(Team::class)
        ->tenantRegistration(RegisterTeam::class)
        ->tenantProfile(EditTeamProfile::class)
        ->pages([
            RegisterTeam::class,
            EditTeamProfile::class,
        ]);
}
```

These pages automatically:
- Use `BrandingSchema` for consistent form fields
- Handle team creation with user attachment
- Support both `users()` and `members()` relationship methods
- Use configurable team model (defaults to `App\Models\Team`)

**Configuration:**

You can customize the team model class via config:

```php
// config/filament-tenancy.php
return [
    'team_model' => \App\Models\Team::class,
];
```

### Team Settings Page (Custom)

If you need custom pages, the package provides a `BrandingSchema` helper to easily create team profile forms with consistent branding fields.

#### Option 1: Using the Complete Base Schema (Recommended)

For a complete team profile page with name, slug, and branding fields:

```php
use App\Models\Team;
use BeeGoodIT\FilamentTenancy\Filament\Schemas\BrandingSchema;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditTeamProfile extends EditTenantProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(
                BrandingSchema::getBaseSchema(Team::class)
            );
    }
}
```

This includes:
- `name` field (required, max 255 characters)
- `slug` field (required, max 255 characters, unique)
- Branding section with logo, primary_color, and secondary_color

#### Option 2: Using Only the Branding Section

If you need to add custom fields or sections, you can use just the branding section:

```php
use BeeGoodIT\FilamentTenancy\Filament\Schemas\BrandingSchema;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditTeamProfile extends EditTenantProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Team::class, 'slug', ignoreRecord: true),
                
                BrandingSchema::getBrandingSection(),
                
                // Add your custom sections here
                Section::make('Settings')
                    ->schema([
                        // Your custom fields
                    ]),
            ]);
    }
}
```

The branding section includes:
- Logo upload (FileUpload with S3/public disk support, max 2MB, image types)
- Primary brand color (ColorPicker)
- Secondary color (ColorPicker, optional)

### Find Team by OAuth Tenant

```php
$team = Team::whereOAuthTenant('microsoft', $tenantId)->first();
```

## Dynamic Theming

The package provides a helper for applying dynamic team colors via CSS variables. This approach prevents issues with avatar URLs and other Filament components that expect hex colors, while still allowing dynamic theming.

### Why This Pattern?

- **Static Default Color**: Always use a static color in `->colors()` to prevent avatar URL issues (ui-avatars.com doesn't understand oklch format)
- **CSS Variables**: Use render hook for dynamic theming via CSS variables
- **Hex Storage**: Colors stored as hex strings in database (not oklch)
- **Conversion**: Convert hex to oklch palette only for CSS variables

### Usage

#### Option 1: Using the Helper Trait (Recommended)

```php
use BeeGoodIT\FilamentTenancy\Filament\Concerns\AppliesTeamTheme;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

class YourPanelProvider extends PanelProvider
{
    use AppliesTeamTheme;

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->colors([
                'primary' => Color::Cyan,  // Static default (prevents avatar URL issues)
            ])
            ->tenant(Team::class)
            ->brandName(fn () => filament()->getTenant()?->name ?? 'Your App')
            ->brandLogo(fn () => filament()->getTenant()?->getFilamentLogoUrl() ?? asset('images/logo.svg'))
            ->applyTeamTheme('#00ffff');  // Default primary color (cyan)
    }
}
```

With optional secondary color:

```php
->applyTeamTheme('#00ffff', '#ff0080');  // Primary and secondary defaults
```

#### Option 2: Manual Render Hook

If you need more control, you can use the `ThemeRenderer` directly:

```php
use BeeGoodIT\FilamentTenancy\Filament\ThemeRenderer;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

public function panel(Panel $panel): Panel
{
    return $panel
        ->colors([
            'primary' => Color::Cyan,
        ])
        ->renderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => Blade::render(
                ThemeRenderer::getTemplate('#00ffff', '#ff0080')
            )
        );
}
```

### How It Works

1. Panel uses static default color in `->colors()` (for Filament internals)
2. Render hook injects CSS variables `--primary-{shade}` and `--secondary-{shade}` based on tenant colors
3. Your CSS can use these variables: `color: var(--primary-500);`
4. When tenant has no colors set, defaults are used
5. Colors are automatically converted from hex (database) to oklch (CSS variables)

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

