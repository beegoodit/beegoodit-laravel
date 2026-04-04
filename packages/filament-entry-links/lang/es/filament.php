<?php

return [
    'navigation_group' => 'Marketing',
    'model' => [
        'label' => 'Enlace de entrada',
        'plural' => 'Enlaces de entrada',
    ],
    'redirect_code' => [
        'permanent' => '301 — Movido permanentemente (Moved Permanently)',
        'temporary' => '302 — Encontrado (redirección temporal)',
    ],
    'form' => [
        'token' => 'Token',
        'token_auto_hint' => 'Se rellena un token aleatorio; puedes cambiarlo para impresión o códigos QR.',
        'slug' => 'Slug (opcional, solo cosmético en la URL)',
        'target_url' => 'URL de destino',
        'redirect_code' => 'Tipo de redirección',
        'is_enabled' => 'Activo',
        'active_from' => 'Activo desde',
        'active_to' => 'Activo hasta',
        'notes' => 'Notas',
    ],
    'infolist' => [
        'public_url' => 'URL pública de entrada',
        'qr_code' => 'Código QR',
        'qr_code_a11y' => 'Código QR para la URL pública de entrada',
        'created_at' => 'Creado',
        'updated_at' => 'Última actualización',
    ],
    'table' => [
        'token' => 'Token',
        'slug' => 'Slug',
        'target_url' => 'URL de destino',
        'redirect_code' => 'Redirección',
        'is_enabled' => 'Activo',
        'active_from' => 'Activo desde',
        'active_to' => 'Activo hasta',
        'active_window_always' => 'Siempre (sin límite)',
        'created_at' => 'Creado',
    ],
];
