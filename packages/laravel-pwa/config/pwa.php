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

    'subscription_model' => \BeeGoodIT\LaravelPwa\Models\PushSubscription::class,
];
