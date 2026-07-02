<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Push Notifications
    |--------------------------------------------------------------------------
    |
    | Configure web push notifications. Generate VAPID keys using:
    | php artisan pwa:vapid-keys
    |
    */

    'push' => [
        'enabled' => env('PWA_PUSH_ENABLED', true),

        'vapid' => [
            'subject' => env('VAPID_SUBJECT', 'mailto:contact@example.com'),
            'public_key' => env('VAPID_PUBLIC_KEY'),
            'private_key' => env('VAPID_PRIVATE_KEY'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Subscription Middleware
        |--------------------------------------------------------------------------
        |
        | Default middleware for the push subscription routes. Use 'web' to
        | support sessions/auth, or 'api' for stateless tokens.
        |
        */
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Subscription Model
    |--------------------------------------------------------------------------
    |
    | The model used for storing push subscriptions. You can extend this
    | model if you need custom functionality.
    |
    */

    'subscription_model' => \BeegoodIT\LaravelPwa\Models\Notifications\PushSubscription::class,

    /*
    |--------------------------------------------------------------------------
    | Notifications Infrastructure
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'delivery_retention_days' => 30,

        'queue' => 'default',

        'rate_limit' => [
            'pushes_per_minute' => 50,
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Push Notification Teaser (Soft Prompts)
    |--------------------------------------------------------------------------
    */
    'teaser' => [
        'url' => '/me/notifications',
        'dismiss_duration' => 7, // days
    ],

    /*
    |--------------------------------------------------------------------------
    | PWA Navigation (optional bottom bar + menu sheet)
    |--------------------------------------------------------------------------
    |
    | Include `@pwaStyles` in your layout <head> — ships bundled CSS for the
    | bottom bar and menu sheet (no Tailwind build required).
    |
    | Set 'bar' to an array of items or a closure that returns items. Each
    | item: label, icon (Heroicon name when Filament is present), url,
    | optional active (bool), optional action (e.g. 'toggleMenu' for menu button).
    | Use the <x-pwa::nav> component and pass the menu slot for sheet content.
    |
    | Theming: define `--pwa-*` surface tokens in your app CSS (recommended), or set
    | `theme_tokens` below when you cannot edit CSS yet. See README § Migration.
    | Legacy Tailwind class strings remain supported for advanced custom setups.
    |
    */
    'navigation' => [
        'padding_bottom' => '4rem',
        'bar' => [],
        'theme_tokens' => [
            'light' => [
                // 'text-active' => '#18181b',
                // 'text-muted' => '#71717a',
                // 'text-hover' => '#3f3f46',
                // 'surface' => 'rgba(255, 255, 255, 0.9)',
                // 'surface-border' => 'rgba(228, 228, 231, 0.5)',
                // 'sheet-surface' => '#ffffff',
                // 'sheet-title' => '#18181b',
                // 'sheet-border' => '#e4e4e7',
                // 'backdrop' => 'rgba(24, 24, 27, 0.75)',
            ],
            'dark' => [
                // 'text-active' => '#fafafa',
                // 'surface' => 'rgba(24, 24, 27, 0.9)',
            ],
        ],
        'active_color_class' => null,
        'bar_class' => null,
        'bar_item_inactive_class' => null,
        'bar_item_hover_class' => null,
        'sheet_backdrop_class' => null,
        'sheet_panel_class' => null,
        'sheet_header_border_class' => null,
        'sheet_title_class' => null,
        'sheet_close_class' => null,
        'register_filament_styles' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | PWA Top Nav (optional fixed header)
    |--------------------------------------------------------------------------
    |
    | Optional fixed top bar for logo + actions. Use <x-pwa::header> and pass
    | your content via the default slot. header_class controls the bar look;
    | padding_top is applied to main, .fi-main, .fi-sidebar so content clears.
    |
    */
    'header' => [
        'header_class' => 'fixed top-0 inset-x-0 z-[105] bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm',
        'padding_top' => '5rem',
    ],
];
