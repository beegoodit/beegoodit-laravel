# BeeGoodIT Laravel PWA

Progressive Web App support for Laravel with Manifest, Service Worker, and **Web Push Notifications**.

### Web Push Notifications
This package provides a built-in notification channel and a set of UI components to handle the subscription process.

#### 1. Push Subscription Logic
The package automatically handles subscription storage and removal via the `PushSubscriptionController`.
By default, subscriptions are linked to the authenticated user.

#### 2. Early Push Prompt (Soft Prompt)
Modern PWA standards recommend a **"Two-Step Opt-in"** process. Instead of showing the browser's native (and uncustomizable) permission prompt on page load, use a **Soft Prompt** (Teaser) to explain the value of notifications.

---

## Architecture & Data Flow

The system is designed as an **Intelligent Recorder and Worker**. It leverages Laravel's native notification system but interjects a tracking and control layer.

### Data Flow (Decoupled Delivery)

```mermaid
sequenceDiagram
    participant App
    participant Laravel
    participant PWA_C as WebPushChannel
    participant PWA_DB as Database
    participant PWA_J as SendMessageJob
    participant Push as Push Service

    App->>Laravel: Notification::send($users, $notification)
    Laravel->>PWA_C: send($notifiable, $notification)
    
    Note over PWA_C: RECORDING PHASE
    PWA_C->>PWA_DB: Create Message (Status: "pending")
    PWA_C->>PWA_J: Dispatch Job
    
    Note over PWA_J: WORKER PHASE
    PWA_J->>PWA_DB: Fetch Message Status
    
    alt Status is "on_hold"
        Note over PWA_J: STOP (Skip)
    else Status is "pending"
        alt pwa_deliver_notifications is FALSE (System Stop)
            PWA_J->>PWA_J: release($delay) (Re-queue)
            Note over PWA_J: Wait & Try Again
        else pwa_deliver_notifications is TRUE (Open)
            PWA_J->>Push: deliverPayload()
            PWA_J->>PWA_DB: Update Status: "sent"
        end
    end
```

---

## System Control

### 1. Delivery Gate (Global Switch)
The system has a global "Delivery Gate" controlled by the `pwa_deliver_notifications` setting.
- **Enabled**: Workers process notifications immediately.
- **Disabled**: Workers will re-queue jobs with a delay, effectively pausing all PWA delivery without losing data or changing message statuses.

**Control Options:**
- **Filament**: Use the **Notification Settings** page in the Admin panel.
- **Artisan**: `php artisan pwa:toggle-system off|on`

### 2. Manual Management
Admins have full control over recorded messages and broadcasts:
- **Messages**: Edit content (title/body), manually toggle `on_hold`/`pending` status, or delete records entirely (even if sent).
- **Broadcasts**: Hold or release ALL pending messages for a specific broadcast at once, delete broadcasts (cascading to messages), or resend completed broadcasts.

---

## Installation

```bash
composer require beegoodit/laravel-pwa
```

## Setup

### 1. Add Trait to User Model
Add the `HasPushSubscriptions` trait to your `App\Models\User` model.

### 2. Publish and Migrate
```bash
php artisan vendor:publish --tag=pwa-config
php artisan vendor:publish --tag=pwa-migrations
php artisan migrate
```

### 3. Generate VAPID Keys
```bash
php artisan pwa:vapid-keys
```

### 4. Filament Integration
Register the `LaravelPwaPlugin` in your `PanelProvider`:

```php
->plugins([
    \BeegoodIT\LaravelPwa\Filament\LaravelPwaPlugin::make(),
])
```

This will automatically register:
- **Notification Settings**: Control global delivery.
- **Broadcasts**: Send manual bulk notifications.
- **Messages**: Detailed delivery log and manual hold/release controls.
- **Subscriptions**: Transparency into active browser clients and diagnostic tests.

### 5. Optional: PWA navigation (bottom bar + menu sheet)

For standalone PWA mode, the browser hides the URL bar. You can add an in-app bottom navigation bar and slide-up menu using the `<x-pwa::nav>` component.

1. **Configure bar items** in `config/pwa.php` under `navigation.bar`: set an array of items or a closure that returns items. Each item: `label`, `icon` (Heroicon name when Filament is present), `url`, optional `active` (bool), optional `action` (e.g. `'toggleMenu'` for the menu button).
2. **Include the component** in your layout and pass the **menu** slot with your content (auth links, legal, etc.):

```blade
<x-pwa::nav :items="value(config('pwa.navigation.bar'))" :menu-title="__('Menu')">
    <x-slot:menu>
        @auth
            <a href="{{ url('/me/profile') }}">...</a>
        @else
            <a href="{{ route('login') }}">{{ __('Log In') }}</a>
        @endauth
    </x-slot:menu>
</x-pwa::nav>
```

Icons use Filament’s Heroicons when available; otherwise a simple fallback is shown. Set `navigation.active_color_class` (e.g. `text-primary-600 dark:text-primary-400`) to match your theme; default is amber. The nav adds padding to `main`, `.fi-main`, and `.fi-sidebar` for use on Filament dashboards. Default `navigation.bar` is `[]` (opt-in).

**Theming with Tailwind:** You can override the nav look with optional Tailwind class strings under `config/pwa.php` → `navigation`. Each key defaults to the current look; omit any key to keep the default.

| Key | Purpose |
|-----|---------|
| `bar_class` | Bar container (bg, border, shadow) |
| `bar_item_inactive_class` | Inactive tab icon + label |
| `bar_item_hover_class` | Hover state for inactive items |
| `active_color_class` | Active tab and open menu button |
| `sheet_backdrop_class` | Backdrop overlay |
| `sheet_panel_class` | Sheet panel (bg, radius, shadow) |
| `sheet_header_border_class` | Header bottom border |
| `sheet_title_class` | Menu title text |
| `sheet_close_class` | Close button |

Example: set `'active_color_class' => 'text-primary-600 dark:text-primary-400'` to match your app's primary color.

## Features
- ✅ **PWA manifest.json** installation support
- ✅ **Push Notifications** via Web Push (VAPID)
- ✅ **Decoupled Worker** with Global Pause & Manual Hold
- ✅ **Diagnostics** with "Send Test Notification" action
- ✅ **Service Worker** with caching and offline support

## License
MIT License.
