# BeeGoodIT Laravel PWA

Progressive Web App support for Laravel with Manifest, Service Worker, and **Web Push Notifications**.

## Installation

```bash
composer require beegoodit/laravel-pwa
```

## Setup

### 1. Add Trait to User Model
Add the `HasPushSubscriptions` trait to your `App\Models\User` model:

```php
use BeeGoodIT\LaravelPwa\Models\Traits\HasPushSubscriptions;

class User extends Authenticatable {
    use HasPushSubscriptions;
}
```

### 2. Publish and Migrate
```bash
php artisan vendor:publish --tag=pwa-config
php artisan vendor:publish --tag=pwa-migrations
php artisan vendor:publish --tag=pwa-js
php artisan migrate
```

### 3. Generate VAPID Keys
```bash
php artisan pwa:vapid-keys
```

### 4. Configure Layout
Add the Blade directives to your main layout. They handle meta tags, manifest inclusion, and script registration.

```blade
<head>
    ...
    @pwaHead
</head>
<body>
    ...
    @yield('content')
    
    @pwaScripts
</body>
```

### 5. Setup Proxy/HTTPS (If using Tunnel)
If you are using a Cloudflare tunnel or proxy, ensure you trust proxies in `bootstrap/app.php` and force HTTPS in your `AppServiceProvider` if needed.

## Push Notifications

### Artisan Testing
Send a test message to all subscribers:
```bash
php artisan pwa:send --all --title="Hello" --body="Test Message"
```

### Filament Integration
To add the Broadcast UI to your Filament Admin panel, register the page in your `PanelProvider`:

```php
->pages([
    \BeeGoodIT\LaravelPwa\Filament\Pages\BroadcastPushNotification::class,
])
```

### Custom Notifications
Use the `webPush` channel in your standard Laravel Notifications:

```php
public function via($notifiable) {
    return ['webPush'];
}

public function toWebPush($notifiable) {
    return (new \BeeGoodIT\LaravelPwa\Messages\WebPushMessage)
        ->title('New Alert')
        ->body('Something happened!')
        ->action('View', url('/'));
}
```

## Features
- ✅ **PWA manifest.json** installation support
- ✅ **Push Notifications** via Web Push (VAPID)
- ✅ **Filament Admin** broadcast page included
- ✅ **Artisan Command** for manual/test sending
- ✅ **Automatic SW registration** via Blade directives
- ✅ **Service Worker** with caching and offline support

## License
MIT License.
