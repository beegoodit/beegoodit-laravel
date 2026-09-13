# BeeGoodIT Laravel Cookie Consent

GDPR-compliant cookie consent banner for Laravel applications with Livewire.

## Installation

```bash
composer require beegoodit/laravel-cookie-consent
```

## Usage

### 1. Add to Layout

```blade
<!DOCTYPE html>
<html>
<head>
    @livewireStyles
</head>
<body>
    @livewire('cookie-consent')

    @livewireScripts
</body>
</html>
```

TallCMS hosts should **not** Composer-require this package. The marketplace plugin copies the banner at pack time (`scripts/sync-banner.sh`).

### 2. Publish Config (Optional)

```bash
php artisan vendor:publish --tag=cookie-consent-config
```

### 3. Views

Two Blade views ship in the package:

| View | When to use |
|------|-------------|
| `cookie-consent::livewire.cookie-consent` | Default. Tailwind **zinc** utilities (Octake). Add the view path to Tailwind content so classes are not purged. |
| `cookie-consent::livewire.cookie-consent-daisy` | **daisyUI** component classes (`btn`, `card`, `bg-base-100`). Use on TallCMS themes. Do not use `modal`/`modal-box` — theme CSS often does not emit those. |

Default is zinc. Daisy hosts set `view` in published `config/cookie-consent.php` (or in a service provider). TallCMS glue already forces daisy.

## Consent helper

Cookie name: `__cookie_consent`. Values: `essential` | `analytics` | `marketing` | `all`.

```php
use BeegoodIT\LaravelCookieConsent\Consent;

Consent::available();              // visitor has chosen
Consent::allows('analytics');     // GA / Umami / Plausible
Consent::allows('marketing');
Consent::allows('functional');    // true only for `all` in v1
Consent::allows('essential');     // always true

Consent::policyUrlUsing(fn (): ?string => '/datenschutz');
```

**Matrix (v1):** essential is always allowed. Analytics is true for `analytics` and `all`. Marketing is true for `marketing` and `all`. Functional is true **only** for `all`. Until a cookie exists, only essential is allowed.

Policy URL: empty config / resolver returning `''` hides the “learn more” link. Defaults are empty (do not assume `/cookie-policy`).

Ignored paths (banner skipped): `admin`, `admin/*`, `livewire/*`, `install`, `installer` (override `ignored_paths` in config).

## Configuration

```php
return [
    'enabled' => env('COOKIE_CONSENT_ENABLED', true),
    'cookie_name' => '__cookie_consent',
    'cookie_lifetime' => 365,
    'view' => 'cookie-consent::livewire.cookie-consent',
    'policy_url_en' => env('COOKIE_POLICY_URL_EN', ''),
    'policy_url_de' => env('COOKIE_POLICY_URL_DE', ''),
    'ignored_paths' => ['admin', 'admin/*', 'livewire/*', 'install', 'install/*', 'installer', 'installer/*'],
];
```

Settings toggles: essential locked on; functional, analytics, and marketing **default off**.

## Pack into TallCMS plugin

```bash
./scripts/sync-banner.sh \
  /path/to/laravel-cookie-consent \
  /path/to/tallcms-cookie-consent-plugin
```

Copies PHP/views/`en`+`de` lang into ZIP-legal plugin paths. Does **not** copy this service provider, `hr`/`hu`/`ro`, tests, or `vendor/`.

## Requirements

- PHP 8.2+
- Laravel 11–13
- Livewire 3 or 4

## License

MIT License. See [LICENSE](LICENSE) for details.
