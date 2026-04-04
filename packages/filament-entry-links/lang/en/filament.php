<?php

return [
    'navigation_group' => 'Marketing',
    'model' => [
        'label' => 'Entry link',
        'plural' => 'Entry links',
    ],
    'redirect_code' => [
        'permanent' => '301 — Moved Permanently',
        'temporary' => '302 — Found (temporary redirect)',
    ],
    'form' => [
        'token' => 'Token',
        'token_auto_hint' => 'A random token is pre-filled for you; edit it if you need a specific value for printed materials or QR codes.',
        'slug' => 'Slug (optional, cosmetic in URL)',
        'target_url' => 'Target URL',
        'redirect_code' => 'Redirect type',
        'is_enabled' => 'Enabled',
        'active_from' => 'Active from',
        'active_to' => 'Active until',
        'notes' => 'Notes',
    ],
    'infolist' => [
        'public_url' => 'Public entry URL',
        'qr_code' => 'QR code',
        'qr_code_a11y' => 'QR code for the public entry URL',
        'created_at' => 'Created',
        'updated_at' => 'Last updated',
    ],
    'table' => [
        'token' => 'Token',
        'slug' => 'Slug',
        'target_url' => 'Target URL',
        'redirect_code' => 'Redirect',
        'is_enabled' => 'Enabled',
        'active_from' => 'Active from',
        'active_to' => 'Active until',
        'active_window_always' => 'Always (no limit)',
        'created_at' => 'Created',
    ],
];
