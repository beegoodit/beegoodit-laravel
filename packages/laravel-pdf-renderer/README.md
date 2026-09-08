# beegoodit/laravel-pdf-renderer

HTML → PDF rendering (Chromium DevTools / CLI) and **DIN 5008** layouts for Laravel apps.

## Install

Path repository (BeeGood monorepo):

```json
{
  "require": {
    "beegoodit/laravel-pdf-renderer": "@dev"
  },
  "repositories": [
    {
      "type": "path",
      "url": "../../composer/beegoodit-laravel/packages/*"
    }
  ]
}
```

```bash
composer require beegoodit/laravel-pdf-renderer:@dev
```

The service provider auto-registers. Publish config optionally:

```bash
php artisan vendor:publish --tag=laravel-pdf-renderer-config
```

## Env (`BEEGOODIT_PDF_RENDER_*`)

| Variable | Purpose |
|----------|---------|
| `BEEGOODIT_PDF_RENDER_FAKE` | Return fake PDF bytes (tests / no Chromium) |
| `BEEGOODIT_PDF_RENDER_EXECUTABLE` | Chromium/Chrome binary path |
| `BEEGOODIT_PDF_RENDER_NO_SANDBOX` | Pass `--no-sandbox` (default `true`) |
| `BEEGOODIT_PDF_RENDER_TIMEOUT` | Process timeout seconds (default `60`) |
| `BEEGOODIT_PDF_RENDER_DEBUG_LAYOUT` | Outline DIN zones + Folgeseite chrome bands with colored borders |

Fallbacks: `CHROMIUM_PATH`, `GROVER_EXECUTABLE_PATH`, common `/usr/bin/chromium*` paths.

In `testing`, the package always binds `FakeStrategy`.

### Layout debug

Set `BEEGOODIT_PDF_RENDER_DEBUG_LAYOUT=true`, or pass `debugLayout: true` to a layout Action.

| Color | Zone |
|-------|------|
| Red | Briefkopf (first page + Folgeseite fixed logo band) |
| Orange | Rucksendeangabe |
| Yellow | Vermerkzone |
| Green | Anschriftzone / Folgeseite `.textfeld` body |
| Purple | Informationsblock |
| Blue | First-page textfeld / Chromium page-footer chrome |

## Trusted HTML

Layout slots (`bodyHtml`, `briefkopfHtml`, `anschriftHtml`, …) are inserted with `{!! !!}`. Callers must pass **trusted HTML** only (app-rendered Blade that escapes user text).

`logoDataUri` must be a **base64** `data:image/(png|jpeg|gif|webp|svg+xml)` URI (or null). Invalid values throw `InvalidArgumentException`.

When `displayHeaderFooter` is required (DIN layouts), DevTools failure **does not** fall back to CLI — that would drop page numbers.

## Render

```php
use BeegoodIT\Pdf\Contracts\RendererContract;

$pdf = app(RendererContract::class)->htmlToPdf($html, $printOptions);
```

Strategies: `ChromiumStrategy`, `FakeStrategy`.

## DIN 5008 layouts

Strict Actions + Spatie Data — no facades.

### First page (Form A default, Form B optional)

```php
use BeegoodIT\Pdf\Actions\Din5008\BuildFirstPageLayoutAction;

$layout = BuildFirstPageLayoutAction::run(
    form: 'A',              // or 'B'
    noMarkers: false,       // Falt-/Lochmarken default on
    briefkopfHtml: $logo,
    rucksendeangabeHtml: $returnAddress,
    vermerkHtml: $note,
    anschriftHtml: $address,
    informationsblockHtml: $info,
    textfeldHtml: $body,
    locale: 'de',
);

$pdf = app(RendererContract::class)->htmlToPdf($layout->html, $layout->printOptions);
```

### Folgeseite (continuation / reports)

Fixed **A4** page box with **absolute/fixed mm zones** (same idea as [din-5008-css](https://github.com/Xiphe/din-5008-css)):

- `.briefkopf` — `position: fixed`, 27 mm, logo right (repeats every printed page)
- `.textfeld` / `.body` — content band (padding `27mm 20mm 0 25mm`; bottom reserved via Chromium `marginBottom`)
- Live `Seite X von Y` — Chromium **footer** only (pageNumber spans; CDP required)

```php
use BeegoodIT\Pdf\Actions\Din5008\BuildFolgeseiteLayoutAction;

$layout = BuildFolgeseiteLayoutAction::run(
    bodyHtml: $body,
    logoDataUri: $logoDataUri,
    locale: 'de',
);

$pdf = app(RendererContract::class)->htmlToPdf($layout->html, $layout->printOptions);
```

## Tests

```bash
cd packages/laravel-pdf-renderer && composer install && ./vendor/bin/pest
```
