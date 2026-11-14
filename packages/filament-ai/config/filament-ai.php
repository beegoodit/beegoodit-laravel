<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    'default_provider' => 'openai',

    'default_model' => null,

    'default_mode' => 'ask',

    'timeout' => 120,

    /*
    |--------------------------------------------------------------------------
    | Chat
    |--------------------------------------------------------------------------
    */

    'chat' => [
        'streaming' => true,
        /*
         * Models offered in the composer select. Values are both option value
         * and label unless you use ['id' => 'Label'] pairs.
         */
        'models' => [
            'gpt-4o-mini',
            'gpt-4o',
            'gpt-4.1-mini',
            'gpt-4.1',
            'gpt-5-mini',
            'gpt-5',
            'claude-haiku-4-5',
            'claude-sonnet-4-5',
            'claude-opus-4-5',
            'gemini-2.5-flash',
            'gemini-2.5-pro',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Attachments (private disk — local or S3 via FILESYSTEM_DISK)
    |--------------------------------------------------------------------------
    */

    'attachments' => [
        'disk' => env('FILAMENT_AI_ATTACHMENTS_DISK', env('FILESYSTEM_DISK', 'local')),
        'directory' => 'filament-ai/chat-attachments',
        'max_files' => 10,
        'max_size_kb' => 25600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate limit
    |--------------------------------------------------------------------------
    */

    'rate_limit' => [
        'daily_per_user' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | null / empty → any authenticated user. Set a Gate ability or Spatie
    | permission name to restrict (e.g. filament-ai.manage-settings).
    |
    */

    'permissions' => [
        'manage_settings' => null,
        'use_assistant' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy subject model
    |--------------------------------------------------------------------------
    |
    | Eloquent class written into subject_type when a migration copies an
    | existing site_id or tenant_id. Required only when those columns hold
    | non-null ids. Null ids stay a null subject.
    |
    */

    'subject_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Legacy tenant column
    |--------------------------------------------------------------------------
    |
    | Column copied by the subject migration when it still exists. Also the
    | default column for tool scopeTenant()/scopeSite() against host tables
    | (TallCMS CMS rows use site_id). AI settings no longer store this column.
    |
    */

    'tenant_column' => 'tenant_id',

    /*
    |--------------------------------------------------------------------------
    | User key type
    |--------------------------------------------------------------------------
    |
    | Column type for created_by_user_id on MCP tokens. Set before the first
    | migrate of that table (or run the alter migration after changing).
    |
    | int  (default) — unsignedBigInteger — stock Laravel users
    | uuid           — uuid column — BeeGood HasUuids users, etc.
    |
    */

    'user_key_type' => 'int',

    /*
    |--------------------------------------------------------------------------
    | Tenant resolver
    |--------------------------------------------------------------------------
    |
    | Invokable class-string or callable returning an Eloquent model or null.
    | null → no scope (one installation-wide settings row).
    |
    */

    'tenant_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Remote MCP (soft peer: laravel/mcp)
    |--------------------------------------------------------------------------
    |
    | When laravel/mcp is missing the app still boots; Settings warns and the
    | endpoint is not registered. Token carries tenant scope.
    |
    */

    'mcp' => [
        'enabled_by_default' => false,
        'path' => '/ai/mcp',
        'server_name' => 'Filament AI',
        'tokens_table' => 'beegoodit_filament_ai_mcp_tokens',
        'token_prefix' => 'fa_mcp_',
        'rate_limit' => [
            'per_token_per_minute' => 120,
            'per_tenant_per_minute' => 600,
            'tools_call_per_minute' => 60,
        ],
        /*
         * Optional callable($request, McpToken $token, Setting $settings, Authenticatable $user): void
         * Hosts (e.g. TallCMS Multisite) may pin extra request context here.
         */
        'after_authenticate' => null,
    ],

];
