# BeeGoodIT Laravel PWA

Progressive Web App support for Laravel applications with manifest, service worker, and installability.

## Installation

```bash
composer require beegoodit/laravel-pwa
```

## Setup

### 1. Publish Assets

```bash
php artisan vendor:publish --tag=pwa-manifest
php artisan vendor:publish --tag=pwa-service-worker
php artisan vendor:publish --tag=pwa-views
```

### 2. Customize Manifest

Edit `public/manifest.json`:

```json
{
  "name": "Your App Name",
  "short_name": "App",
  "description": "Your app description",
  "theme_color": "#000000",
  "background_color": "#ffffff"
}
```

### 3. Add Icons

Place icons in `public/icons/`:
- icon-48x48.png
- icon-72x72.png
- icon-96x96.png
- icon-144x144.png
- icon-180x180.png
- icon-192x192.png
- icon-512x512.png

### 4. Include Meta Tags

Add to your layout's `<head>`:

```blade
@include('laravel-pwa::partials.pwa-meta')
```

## Features

- ✅ PWA manifest.json
- ✅ Service worker with caching
- ✅ Install prompt handling
- ✅ Offline support
- ✅ App icon templates
- ✅ Apple touch icons

## Service Worker

The service worker:
- Caches app shell (CSS, JS)
- Caches static assets automatically
- Serves from cache first
- Falls back to network
- Cleans old caches on update

## Customization

### Update Cache Version

Edit `public/sw.js`:

```javascript
const CACHE_NAME = 'my-app-v2'; // ← Change version to bust cache
```

### Add More Cached URLs

```javascript
const urlsToCache = [
  '/',
  '/build/assets/app.css',
  '/build/assets/app.js',
  '/images/logo.svg',  // ← Add more
];
```

## Testing

Test PWA features:
1. Open DevTools → Application → Manifest
2. Check service worker registration
3. Test offline mode
4. Test install prompt

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+

## License

MIT License. See [LICENSE](LICENSE) for details.

