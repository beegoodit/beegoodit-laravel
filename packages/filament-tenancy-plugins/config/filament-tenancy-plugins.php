<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant model
    |--------------------------------------------------------------------------
    |
    | Class-string of the tenant model. Required for Artisan assign
    | and revoke with --tenant, and for resolving slugs in tenancy-plugins:list.
    |
    */

    'tenant_model' => env('FILAMENT_TENANCY_PLUGINS_TENANT_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | Slug attribute
    |--------------------------------------------------------------------------
    |
    | Attribute used to resolve --tenant= on Artisan commands, falling back
    | to the model's primary key.
    |
    */

    'slug_attribute' => env('FILAMENT_TENANCY_PLUGINS_SLUG_ATTRIBUTE', 'slug'),

    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | Class-string of the user model. Required for Artisan assign and revoke
    | with --user, and for resolving emails in tenancy-plugins:list.
    |
    */

    'user_model' => env('FILAMENT_TENANCY_PLUGINS_USER_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | User lookup attribute
    |--------------------------------------------------------------------------
    |
    | Attribute used to resolve --user= on Artisan commands (case-insensitive),
    | falling back to the model's primary key.
    |
    */

    'user_lookup_attribute' => env('FILAMENT_TENANCY_PLUGINS_USER_LOOKUP_ATTRIBUTE', 'email'),

];
