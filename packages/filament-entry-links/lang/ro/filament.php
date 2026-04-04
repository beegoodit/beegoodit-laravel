<?php

return [
    'navigation_group' => 'Marketing',
    'model' => [
        'label' => 'Link de intrare',
        'plural' => 'Linkuri de intrare',
    ],
    'redirect_code' => [
        'permanent' => '301 — Mutat permanent (Moved Permanently)',
        'temporary' => '302 — Găsit (redirecționare temporară)',
    ],
    'form' => [
        'token' => 'Token',
        'token_auto_hint' => 'Se completează automat un token aleatoriu; îl puteți modifica pentru tipărire sau coduri QR.',
        'slug' => 'Slug (opțional, doar pentru afișare în URL)',
        'target_url' => 'URL țintă',
        'redirect_code' => 'Tip redirecționare',
        'is_enabled' => 'Activ',
        'active_from' => 'Activ de la',
        'active_to' => 'Activ până la',
        'notes' => 'Note',
    ],
    'infolist' => [
        'public_url' => 'URL public de intrare',
        'qr_code' => 'Cod QR',
        'qr_code_a11y' => 'Cod QR pentru URL-ul public de intrare',
        'created_at' => 'Creat',
        'updated_at' => 'Ultima actualizare',
    ],
    'table' => [
        'token' => 'Token',
        'slug' => 'Slug',
        'target_url' => 'URL țintă',
        'redirect_code' => 'Redirecționare',
        'is_enabled' => 'Activ',
        'active_from' => 'Activ de la',
        'active_to' => 'Activ până la',
        'active_window_always' => 'Mereu (fără limită)',
        'created_at' => 'Creat',
    ],
];
