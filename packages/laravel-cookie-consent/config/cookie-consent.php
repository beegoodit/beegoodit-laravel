<?php

return [
    /*
     * Enable or disable the cookie consent banner
     */
    'enabled' => env('COOKIE_CONSENT_ENABLED', true),

    /*
     * Cookie name for storing user's consent
     */
    'cookie_name' => 'cookie_consent',

    /*
     * Cookie lifetime in days
     */
    'cookie_lifetime' => 365,

    /*
     * Position of the banner: 'bottom' or 'top'
     */
    'position' => 'bottom',

    /*
     * Enable analytics cookies
     */
    'analytics_enabled' => env('ANALYTICS_ENABLED', false),

    /*
     * Analytics tracking code (Google Analytics, etc.)
     */
    'analytics_code' => env('ANALYTICS_CODE'),
];

