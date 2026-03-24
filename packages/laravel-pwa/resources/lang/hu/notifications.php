<?php

return [
    'title' => 'Push értesítések',
    'broadcasts' => [
        'title' => 'Broadcastok',
        'resource_label' => 'Broadcast',
        'resource_label_plural' => 'Broadcastok',
        'trigger_type' => 'Kiváltás típusa',
        'status' => 'Státusz',
        'total_recipients' => 'Címzettek',
        'total_sent' => 'Elküldve',
        'total_opened' => 'Megnyitva',
        'content' => 'Tartalom',
        'created_at' => 'Létrehozva',
        'stats' => 'Statisztikák',
    ],
    'messages' => [
        'title' => 'Üzenetek',
        'resource_label' => 'Üzenet',
        'resource_label_plural' => 'Üzenetek',
        'status' => 'Kézbesítési státusz',
        'opened_at' => 'Megnyitva',
        'error' => 'Hibaüzenet',
        'actions' => [
            'hold' => 'Várakoztatás',
            'release' => 'Sorba engedélyezés',
        ],
    ],
    'subscriptions' => [
        'title' => 'Feliratkozások',
        'resource_label' => 'Feliratkozás',
        'resource_label_plural' => 'Feliratkozások',
    ],
    'settings' => [
        'title' => 'Értesítési beállítások',
        'fields' => [
            'pwa_deliver_notifications' => [
                'label' => 'PWA értesítések kézbesítése',
                'description' => 'Letiltáskor az értesítések a háttér sorban maradnak és nem történik kézbesítési kísérlet.',
            ],
        ],
    ],
    'nav' => [
        'group' => 'PWA kezelés',
    ],
];
