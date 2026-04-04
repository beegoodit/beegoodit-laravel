<?php

return [
    'navigation_group' => 'Marketing',
    'model' => [
        'label' => 'Belépési link',
        'plural' => 'Belépési linkek',
    ],
    'redirect_code' => [
        'permanent' => '301 — Állandó átirányítás (Moved Permanently)',
        'temporary' => '302 — Találat (ideiglenes átirányítás)',
    ],
    'form' => [
        'token' => 'Token',
        'token_auto_hint' => 'Véletlenszerű token kerül kitöltésre; nyomtatáshoz vagy QR-kódokhoz módosíthatja.',
        'slug' => 'Slug (opcionális, csak megjelenés az URL-ben)',
        'target_url' => 'Cél URL',
        'redirect_code' => 'Átirányítás típusa',
        'is_enabled' => 'Aktív',
        'active_from' => 'Aktív ettől',
        'active_to' => 'Aktív eddig',
        'notes' => 'Megjegyzések',
    ],
    'infolist' => [
        'public_url' => 'Nyilvános belépési URL',
        'qr_code' => 'QR-kód',
        'qr_code_a11y' => 'QR-kód a nyilvános belépési URL-hez',
        'created_at' => 'Létrehozva',
        'updated_at' => 'Utolsó módosítás',
    ],
    'table' => [
        'token' => 'Token',
        'slug' => 'Slug',
        'target_url' => 'Cél URL',
        'redirect_code' => 'Átirányítás',
        'is_enabled' => 'Aktív',
        'active_from' => 'Aktív ettől',
        'active_to' => 'Aktív eddig',
        'active_window_always' => 'Mindig (korlát nélkül)',
        'created_at' => 'Létrehozva',
    ],
];
