<?php

return [
    'navigation_group' => 'Marketing',
    'model' => [
        'label' => 'Ulazna poveznica',
        'plural' => 'Ulazne poveznice',
    ],
    'redirect_code' => [
        'permanent' => '301 — Trajno premješteno',
        'temporary' => '302 — Pronađeno (privremeno preusmjeravanje)',
    ],
    'form' => [
        'token' => 'Token',
        'token_auto_hint' => 'Nasumični token je unaprijed popunjen; uredite ga ako trebate određenu vrijednost za tiskane materijale ili QR kodove.',
        'slug' => 'Slug (neobavezno, kozmetički u URL-u)',
        'target_url' => 'Odredišni URL',
        'redirect_code' => 'Vrsta preusmjeravanja',
        'is_enabled' => 'Omogućeno',
        'active_from' => 'Aktivno od',
        'active_to' => 'Aktivno do',
        'notes' => 'Bilješke',
    ],
    'infolist' => [
        'public_url' => 'Javni ulazni URL',
        'qr_code' => 'QR kod',
        'qr_code_a11y' => 'QR kod za javni ulazni URL',
        'created_at' => 'Stvoreno',
        'updated_at' => 'Zadnja izmjena',
    ],
    'table' => [
        'token' => 'Token',
        'slug' => 'Slug',
        'target_url' => 'Odredišni URL',
        'redirect_code' => 'Preusmjeravanje',
        'is_enabled' => 'Omogućeno',
        'active_from' => 'Aktivno od',
        'active_to' => 'Aktivno do',
        'active_window_always' => 'Uvijek (bez ograničenja)',
        'created_at' => 'Stvoreno',
    ],
];
