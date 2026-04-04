<?php

return [
    'navigation_group' => 'Marketing',
    'model' => [
        'label' => 'Einstiegslink',
        'plural' => 'Einstiegslinks',
    ],
    'redirect_code' => [
        'permanent' => '301 — Dauerhaft weitergeleitet (Moved Permanently)',
        'temporary' => '302 — Vorübergehend weitergeleitet (Found)',
    ],
    'form' => [
        'token' => 'Token',
        'token_auto_hint' => 'Es wird automatisch ein Token vorgeschlagen; Sie können ihn für Druck oder QR-Codes anpassen.',
        'slug' => 'Slug (optional, nur Anzeige in der URL)',
        'target_url' => 'Ziel-URL',
        'redirect_code' => 'Weiterleitungstyp',
        'is_enabled' => 'Aktiv',
        'active_from' => 'Aktiv ab',
        'active_to' => 'Aktiv bis',
        'notes' => 'Notizen',
    ],
    'infolist' => [
        'public_url' => 'Öffentliche Einstiegs-URL',
        'qr_code' => 'QR-Code',
        'qr_code_a11y' => 'QR-Code für die öffentliche Einstiegs-URL',
        'created_at' => 'Erstellt',
        'updated_at' => 'Zuletzt aktualisiert',
    ],
    'table' => [
        'token' => 'Token',
        'slug' => 'Slug',
        'target_url' => 'Ziel-URL',
        'redirect_code' => 'Weiterleitung',
        'is_enabled' => 'Aktiv',
        'active_from' => 'Aktiv ab',
        'active_to' => 'Aktiv bis',
        'active_window_always' => 'Immer (ohne Grenze)',
        'created_at' => 'Erstellt',
    ],
];
