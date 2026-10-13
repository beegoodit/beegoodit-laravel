<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    'navigation_icon' => 'heroicon-o-sparkles',

    /*
    |--------------------------------------------------------------------------
    | Settings table
    |--------------------------------------------------------------------------
    |
    | TallCMS pack-sync (004) may remap to tallcms_ai_settings.
    |
    */

    'settings_table' => 'beegoodit_filament_ai_settings',

    /*
    |--------------------------------------------------------------------------
    | Enabled column
    |--------------------------------------------------------------------------
    |
    | Package attribute is always `$settings->enabled`. Hosts may map it to a
    | different DB column (TallCMS: is_enabled).
    |
    */

    'enabled_column' => 'enabled',

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
    | Workspace key type
    |--------------------------------------------------------------------------
    |
    | Column type for workspace-scoped tables (settings, conversations, …).
    | Set before the first migrate.
    |
    | int  (default) — unsignedBigInteger — TallCMS Site ids, stock Filament teams
    | uuid           — uuid column — BeeGood filament-tenancy teams, etc.
    |
    */

    'workspace_key_type' => 'int',

    /*
    |--------------------------------------------------------------------------
    | Workspace column
    |--------------------------------------------------------------------------
    |
    | Attribute / column name for workspace scope. TallCMS pack-sync uses site_id.
    |
    */

    'workspace_column' => 'workspace_id',

    /*
    |--------------------------------------------------------------------------
    | Workspace resolver
    |--------------------------------------------------------------------------
    |
    | Invokable class-string or callable returning int|string|null.
    | null → no scope (single-tenant).
    |
    */

    'workspace_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Remote MCP (soft peer: laravel/mcp)
    |--------------------------------------------------------------------------
    |
    | When laravel/mcp is missing the app still boots; Settings warns and the
    | endpoint is not registered. Token carries workspace scope.
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
            'per_workspace_per_minute' => 600,
            'tools_call_per_minute' => 60,
        ],
        /*
         * Optional callable($request, McpToken $token, Setting $settings, Authenticatable $user): void
         * Hosts (e.g. TallCMS Multisite) may pin extra request context here.
         */
        'after_authenticate' => null,
    ],

];
