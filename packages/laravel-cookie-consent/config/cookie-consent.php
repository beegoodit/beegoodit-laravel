<?php

return [
    /*
     * Enable or disable the cookie consent banner
     */
    'enabled' => env('COOKIE_CONSENT_ENABLED', true),

    /*
     * Cookie name for storing user's consent
     */
    'cookie_name' => '__cookie_consent',

    /*
     * Cookie lifetime in days
     */
    'cookie_lifetime' => 365,

    /*
     * Position of the banner: 'bottom' or 'top'
     */
    'position' => 'bottom',

    /*
     * Cookie policy URLs (per locale)
     */
    'policy_url_en' => env('COOKIE_POLICY_URL_EN', '/cookie-policy'),
    'policy_url_de' => env('COOKIE_POLICY_URL_DE', '/cookie-policy'),

    /*
     * GTM event name triggered when consent changes
     */
    'gtm_event' => 'cookie_refresh',

    /*
     * Paths to ignore (banner won't show)
     */
    'ignored_paths' => [],

    /*
     * Secure cookie setting (HTTPS only)
     */
    'cookie_secure' => env('COOKIE_CONSENT_SECURE', true),
];

