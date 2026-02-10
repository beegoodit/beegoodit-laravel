# Filament Knowledge Base

Standardized knowledge base implementation for BeeGoodIT Filament applications, powered by `guava/filament-knowledge-base`.

## Installation

1. Add the package to your `composer.json` (if not already autoloaded via monorepo conventions).
2. Register the `FilamentKnowledgeBasePanelProvider` in your application.

This package provides a pre-configured Filament Panel at `/kb` that serves documentation from `docs/knowledge-base`.

## Quick Start

### 1. Set Up Documentation Structure

```bash
php artisan kb:setup --locale=de  # For German documentation
php artisan kb:setup --locale=en  # For English documentation
```

> [!IMPORTANT]
> **The `kb:setup` command automatically creates BOTH your locale AND the fallback locale (`en` by default).** This is required to prevent redirect loops.

### 2. Enable the Knowledge Base Panel

Register the provider in `bootstrap/providers.php`:

```php
return [
    // ...
    BeeGoodIT\FilamentKnowledgeBase\FilamentKnowledgeBasePanelProvider::class,
];
```

### 3. Install NPM Dependencies

```bash
npm install @tailwindcss/typography
```

### 4. Configure Your Theme

Add the following to your Filament theme CSS file:

```css
@plugin "@tailwindcss/typography";
@source '../../../../vendor/guava/filament-knowledge-base/src/**/*';
@source '../../../../vendor/guava/filament-knowledge-base/resources/views/**/*';
```

Then rebuild your assets:
```bash
npm run build
```

### 5. Integrate with Other Panels (Optional)

To provide access to the Knowledge Base from another panel (e.g., `/portal`):

```php
use Guava\FilamentKnowledgeBase\Plugins\KnowledgeBaseCompanionPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            KnowledgeBaseCompanionPlugin::make()
                ->knowledgeBasePanelId('knowledge-base'),
        ]);
}
```

## Locale Support

### Multi-Locale Setup (Required)

For proper multi-locale support, you need to implement a custom `FlatfileNode` model and configure middleware order correctly.

#### 1. Create Custom FlatfileNode Model

Create `app/Models/FlatfileNode.php`:

```php
<?php

namespace App\Models;

use Guava\FilamentKnowledgeBase\Enums\NodeType;
use Guava\FilamentKnowledgeBase\Models\FlatfileNode as BaseFlatfileNode;
use Illuminate\Support\Facades\App;

class FlatfileNode extends BaseFlatfileNode
{
    /**
     * Include locale in cache file name so each locale gets its own cache.
     * This prevents serving cached data from the wrong locale.
     */
    protected function sushiCacheFileName(): string
    {
        return parent::sushiCacheFileName() . '-' . App::getLocale();
    }

    public function getRows(): array
    {
        $currentLocale = App::getLocale();
        $allRows = collect(parent::getRows());

        // Filter rows to only include the current locale
        $rows = $allRows->filter(function ($row) use ($currentLocale) {
            return ($row['locale'] ?? 'en') === $currentLocale;
        });

        // If no rows for current locale, return a dummy row to satisfy Sushi's schema detection
        if ($rows->isEmpty()) {
            return [[
                'id' => "{$currentLocale}.knowledge-base.dummy",
                'slug' => 'dummy',
                'type' => NodeType::Documentation,
                'path' => '',
                'icon' => null,
                'title' => 'Dummy',
                'order' => 0,
                'active' => false,
                'data' => json_encode([]),
                'parent_id' => null,
                'panel_id' => 'knowledge-base',
                'locale' => $currentLocale,
            ]];
        }

        // Get unique group names from documents for the current locale
        $panelId = 'knowledge-base';
        $uniqueGroups = $rows
            ->where('panel_id', $panelId)
            ->map(function ($row) {
                $data = json_decode($row['data'] ?? '{}', true);
                return $data['group'] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        // Sort groups to ensure welcome/starting group comes first
        $groupPriority = [
            'Erste Schritte' => 0,
            'Getting Started' => 0,
            'Primeros pasos' => 0,
        ];

        $uniqueGroups = $uniqueGroups->sortBy(function ($groupName) use ($groupPriority) {
            return $groupPriority[$groupName] ?? 999;
        })->values();

        // Create Group nodes from unique group names
        // The base plugin rejects NodeType::Group rows, so we create them dynamically
        $index = 0;
        foreach ($uniqueGroups as $groupName) {
            $rows->push([
                'id' => "{$currentLocale}.{$panelId}.group." . md5($groupName),
                'slug' => 'group-' . md5($groupName),
                'type' => NodeType::Group,
                'path' => '',
                'icon' => null,
                'title' => $groupName,
                'order' => $index++,
                'active' => true,
                'data' => json_encode(['group' => $groupName]),
                'parent_id' => null,
                'panel_id' => $panelId,
                'locale' => $currentLocale,
            ]);
        }

        return $rows->values()->toArray();
    }
}
```

#### 2. Configure the Custom Model

Publish and update the config file:

```bash
php artisan vendor:publish --tag="filament-knowledge-base-config"
```

Update `config/filament-knowledge-base.php`:

```php
return [
    'flatfile-model' => \App\Models\FlatfileNode::class,
    // ... other config
];
```

#### 3. SetLocale Middleware Order (Critical)

> [!CRITICAL]
> **The `SetLocale` middleware MUST run BEFORE `SubstituteBindings`** for route binding to work correctly.

```php
// In your KnowledgeBasePanelProvider
->middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    AuthenticateSession::class,
    ShareErrorsFromSession::class,
    VerifyCsrfToken::class,
    \BeegoodIT\FilamentI18n\Middleware\SetLocale::class,  // ← Must be BEFORE SubstituteBindings
    SubstituteBindings::class,              // ← Route binding happens here
    DisableBladeIconComponents::class,
    DispatchServingFilamentEvent::class,
])
```

**Why this order matters:**
- `SubstituteBindings` performs route model binding to resolve `{record}` parameters
- `FlatfileNode::resolveRouteBindingQuery()` filters by `locale` using `App::getLocale()`
- If `SetLocale` runs after `SubstituteBindings`, the locale isn't set yet, causing 404 errors

Without this, the KB will always use the app's default locale, ignoring user preferences, and route binding will fail.

### Multi-Locale File Naming

> [!CAUTION]
> **All locales must use the SAME filename.** Only the content inside is translated.

**Correct (same filename across locales):**
```
docs/knowledge-base/
  en/welcome.md          # title: Welcome
  de/welcome.md          # title: Willkommen
  es/welcome.md          # title: Bienvenido
```

**Incorrect (different filenames):**
```
docs/knowledge-base/
  en/welcome.md          # slug: welcome
  de/willkommen.md       # slug: willkommen ❌ 404 when locale changes!
  es/bienvenido.md       # slug: bienvenido ❌ 404 when locale changes!
```

The slug is derived from the filename. If slugs don't match, navigation breaks when switching locales.

## Documentation Structure

> [!IMPORTANT]
> **The fallback locale folder (`en/` by default) is REQUIRED!** Without it, you will get `ERR_TOO_MANY_REDIRECTS`.

### Correct Structure

```
docs/knowledge-base/
  en/                      # ✅ REQUIRED - Fallback locale (even if your app uses 'de')
    welcome.md
    getting-started/
      installation.md
  de/                      # ✅ Your primary locale (same filenames!)
    welcome.md
    getting-started/
      installation.md
```

### Incorrect Structure (causes redirect loop)

```
docs/knowledge-base/
  general/                 # ❌ NOT a locale code - will cause redirect loop!
    welcome.md
  de/                      # ❌ Missing 'en/' fallback folder!
    content.md
```

## Troubleshooting

### ERR_TOO_MANY_REDIRECTS

This error occurs when:

1. **Missing fallback locale folder** - Create `docs/knowledge-base/en/` even if your app uses a different locale
2. **Invalid folder names** - Use locale codes (`en`, `de`, `es`) not arbitrary names (`general`, `docs`)
3. **Missing theme configuration** - Add the `@source` paths to your theme.css

**Fix:** Run `php artisan kb:setup --locale=YOUR_LOCALE` - this automatically creates both your locale and the fallback locale.

### 404 on Locale Switch or Route Binding

If pages show 404 when switching locales or accessing `/kb/welcome`:

1. **Check middleware order** - `SetLocale` must run BEFORE `SubstituteBindings`
2. **Verify custom model** - Ensure `config/filament-knowledge-base.php` points to your custom `FlatfileNode`
3. **Check file names** - All locale folders must have files with identical names (slug must match across locales)
4. **Clear caches** - Run `php artisan cache:clear` and `php artisan config:clear`

### Cache Issues

After adding or modifying documentation files:
```bash
php artisan cache:clear
php artisan config:clear
```

> [!NOTE]
> The custom `FlatfileNode` model uses locale-aware caching. Each locale has its own Sushi cache file, so changing locales will automatically use the correct cache.

### Validation

This package validates the documentation structure at boot time:
- **Development**: Throws exception if misconfigured
- **Production**: Logs a warning

## Configuration

The panel is available at `/kb` and requires authentication (via `authMiddleware`).

## Multi-Locale Solution Overview

The complete multi-locale solution consists of several components working together:

### Component Breakdown

1. **Locale-Aware Caching** (`sushiCacheFileName()`)
   - Each locale gets its own Sushi cache file
   - Prevents serving cached data from the wrong locale
   - **Without this:** Wrong locale data can be served from cache

2. **Locale Filtering** (`getRows()` filtering)
   - Filters rows to only include the current locale before processing
   - Ensures group creation and navigation are locale-specific
   - **Without this:** Groups/navigation mix locales

3. **Dynamic Group Node Creation** (`getRows()` group creation)
   - The base plugin rejects `NodeType::Group` rows
   - This implementation extracts unique group names from documentation frontmatter
   - Creates `NodeType::Group` records dynamically for navigation
   - **Without this:** Sidebar groups don't appear

4. **Group Ordering** (priority sorting)
   - Prioritizes "Getting Started" groups (Erste Schritte, Getting Started, Primeros pasos)
   - Ensures the welcome page is the first item in the first group
   - **Without this:** Wrong page might be selected as default

5. **Middleware Order** (`SetLocale` before `SubstituteBindings`)
   - Sets locale before route binding happens
   - Allows `resolveRouteBindingQuery()` to filter by the correct locale
   - **Without this:** Route binding fails (404 errors)

6. **Configuration** (`config/filament-knowledge-base.php`)
   - Points the plugin to the custom `FlatfileNode` model
   - **Without this:** Custom model isn't used

### Why All Components Are Needed

All components are required for a working multi-locale knowledge base:
- **Locale-aware caching** prevents cache pollution
- **Locale filtering** ensures correct data is shown
- **Dynamic group creation** makes navigation work
- **Group ordering** ensures correct default page
- **Middleware order** makes route binding work
- **Configuration** enables the custom model

Together, these ensure:
- ✅ Each locale has its own cache
- ✅ Navigation shows only the current locale
- ✅ Route binding works correctly
- ✅ Sidebar groups are translated and visible
- ✅ Default redirect works properly
