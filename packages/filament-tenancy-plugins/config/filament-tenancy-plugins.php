<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant model
    |--------------------------------------------------------------------------
    |
    | Class-string of the tenant model. Required for Artisan assign
    | and revoke, and for resolving slugs in tenancy-plugins:list.
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

];
