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
    | Set 'bar' to an array of items or a closure that returns items. Each
    | item: label, icon (Heroicon name when Filament is present), url,
    | optional active (bool), optional action (e.g. 'toggleMenu' for menu button).
    | Use the <x-pwa::nav> component and pass the menu slot for sheet content.
    |
    | Theming: Optional Tailwind class strings. Omit any key to use the default.
    | - bar_class: Bar container (bg, border, shadow)
    | - bar_item_inactive_class: Inactive tab icon + label
    | - bar_item_hover_class: Hover state for inactive items
    | - active_color_class: Active tab and open menu button
    | - sheet_backdrop_class: Backdrop overlay
    | - sheet_panel_class: Sheet panel (bg, radius, shadow)
    | - sheet_header_border_class: Header bottom border
    | - sheet_title_class: Menu title text
    | - sheet_close_class: Close button
    |
    */
    'navigation' => [
        'padding_bottom' => '4rem',
        'bar' => [],
        'active_color_class' => 'text-amber-500',
        'bar_class' => 'bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border-t border-gray-200/50 dark:border-gray-800/50 shadow-[0_-1px_10px_rgba(0,0,0,0.05)]',
        'bar_item_inactive_class' => 'text-gray-500 dark:text-gray-400',
        'bar_item_hover_class' => 'group-hover:text-amber-400',
        'sheet_backdrop_class' => 'bg-gray-500/75 dark:bg-gray-900/75',
        'sheet_panel_class' => 'bg-white dark:bg-gray-900 rounded-t-2xl shadow-xl',
        'sheet_header_border_class' => 'border-gray-200 dark:border-gray-800',
        'sheet_title_class' => 'text-gray-900 dark:text-white',
        'sheet_close_class' => 'text-gray-400 hover:text-gray-500 dark:hover:text-gray-300',
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
