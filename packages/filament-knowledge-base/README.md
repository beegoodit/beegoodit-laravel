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

### 3. Configure Your Theme

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

### 4. Integrate with Other Panels (Optional)

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

## Documentation Structure

> [!IMPORTANT]
> **The fallback locale folder (`en/` by default) is REQUIRED!** Without it, you will get `ERR_TOO_MANY_REDIRECTS`.

### Correct Structure

```
docs/knowledge-base/
  en/                      # ✅ REQUIRED - Fallback locale (even if your app uses 'de')
    welcome.md
  de/                      # ✅ Your primary locale
    willkommen.md
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

### Validation

This package validates the documentation structure at boot time:
- **Development**: Throws exception if misconfigured
- **Production**: Logs a warning

## Configuration

The panel is available at `/kb` and requires authentication (via `authMiddleware`).
