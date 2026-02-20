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
    <!-- ... -->
    @livewireStyles
</head>
<body>
    @livewire('cookie-consent')
    
    <!-- Your content -->
    
    @livewireScripts
</body>
</html>
```

### 2. Publish Config (Optional)

```bash
php artisan vendor:publish --tag=cookie-consent-config
```

### 3. Customize (Optional)

```bash
php artisan vendor:publish --tag=cookie-consent-views
```

### 4. Styling (Tailwind CSS)

The banner is styled with Tailwind CSS utility classes only; no separate CSS file is shipped. Your app must include the package view path in Tailwind’s content/source so those classes are not purged.

- **Tailwind v4:** In your main CSS file (e.g. `resources/css/app.css`), add:

  ```css
  @source '../../vendor/beegoodit/laravel-cookie-consent/resources/views/**/*.blade.php';
  ```

- **Tailwind v3:** In `tailwind.config.js`, add the path to the `content` array:

  ```js
  content: [
      // ... your paths
      './vendor/beegoodit/laravel-cookie-consent/resources/views/**/*.blade.php',
  ],
  ```

If your app does not use Tailwind, publish the views (`php artisan vendor:publish --tag=cookie-consent-views`) and replace the utility classes with your own CSS.

## Configuration

```php
// config/cookie-consent.php
return [
    'enabled' => env('COOKIE_CONSENT_ENABLED', true),
    'cookie_lifetime' => 365, // days
    'position' => 'bottom', // or 'top'
    'analytics_enabled' => env('ANALYTICS_ENABLED', false),
];
```

## Features

- ✅ GDPR-compliant consent banner
- ✅ Customizable styling (Tailwind CSS)
- ✅ Accept/Decline options
- ✅ Cookie preference storage
- ✅ Top or bottom positioning
- ✅ Alpine.js transitions
- ✅ Livewire component

## Checking Consent

```php
$hasConsented = request()->cookie('cookie_consent') === 'accepted';

if ($hasConsented) {
    // Load analytics
}
```

## Translation

Create translation files:

```php
// lang/en/cookie-consent.php
return [
    'messages' => [
        'title' => 'We use cookies',
        'description' => '...',
        'accept' => 'Accept',
        'decline' => 'Decline',
    ],
];
```

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Livewire 3.0+

## License

MIT License. See [LICENSE](LICENSE) for details.

