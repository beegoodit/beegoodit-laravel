# BeeGoodIT Laravel PWA

Progressive Web App support for Laravel with Manifest, Service Worker, and **Web Push Notifications**.

### Web Push Notifications
This package provides a built-in notification channel and a set of UI components to handle the subscription process.

#### 1. Push Subscription Logic
The package automatically handles subscription storage and removal via the `PushSubscriptionController`.
By default, subscriptions are linked to the authenticated user.

#### 2. Early Push Prompt (Soft Prompt)
Modern PWA standards recommend a **"Two-Step Opt-in"** process. Instead of showing the browser's native (and uncustomizable) permission prompt on page load, use a **Soft Prompt** (Teaser) to explain the value of notifications.

##### Usage
Include the `@pwaStyles` directive in your layout's `<head>` and `@pwaScripts` before the closing `</body>` tag.

```blade
<head>
    @pwaStyles
</head>
<body>
    ...
    @pwaScripts
</body>
```

To display the push notification teaser, you can use the Blade component:

```blade
<x-pwa::push_prompt_teaser position="fixed-bottom" />
```

##### Features
*   **Automatic Translations**: Supports English, German, and Spanish out of the box. Edit via `php artisan vendor:publish --tag=pwa-lang`.
*   **Theme Integration**: Automatically inherits your app's CSS variables (e.g., `--color-primary-600`, `--font-sans`).
*   **Dark Mode**: Built-in support for `prefers-color-scheme: dark` and `.dark` classes.
*   **Dismissal Logic**: Remembers dismissal in `localStorage` (default: 7 days).

##### Component API
| Prop | Type | Default | Description |
| --- | --- | --- | --- |
| `title` | `string` | `__(...)` | The title. Uses `laravel-pwa::teaser.title` by default. |
| `message` | `string` | `__(...)` | The description. Uses `laravel-pwa::teaser.message`. |
| `buttonText` | `string` | `__(...)` | Button text. Uses `laravel-pwa::teaser.button`. |
| `url` | `string` | `config('pwa.teaser.url')` | Where to redirect (default: `/me/notifications`). |
| `dismissible` | `bool` | `true` | Show/hide the close button. |
| `position` | `string` | `"inline"` | `"inline"` or `"fixed-bottom"`. |

##### Theming & CSS Variables
You can customize the teaser's look by overriding these variables in your `app.css`:

```css
:root {
    --pwa-teaser-bg: #ffffff;
    --pwa-teaser-button-bg: #f59e0b; /* Orange */
}
```

##### UX Best Practices
1. **Context is King:** Don't prompt on page load. Prompt when the user expresses interest (e.g., after favoring a location or viewing tournament results).
2. **Explain the Value:** If the default text isn't relevant, use the component slots to provide specific value propositions.
3. **Respect Dismissal:** The component automatically handles a generic dismissal window to avoid pestering users.

---

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

> **Troubleshooting:** If you get `Unable to create the key`, set the OpenSSL config path:
> ```bash
> OPENSSL_CONF=/etc/ssl/openssl.cnf php artisan pwa:vapid-keys
> ```

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
