<?php

return [
    'title' => 'Notificări Push',
    'broadcasts' => [
        'title' => 'Broadcast-uri',
        'resource_label' => 'Broadcast',
        'resource_label_plural' => 'Broadcast-uri',
        'trigger_type' => 'Tip declanșare',
        'status' => 'Status',
        'total_recipients' => 'Destinatari',
        'total_sent' => 'Trimise',
        'total_opened' => 'Deschise',
        'content' => 'Conținut',
        'created_at' => 'Creat la',
        'stats' => 'Statistici',
    ],
    'messages' => [
        'title' => 'Mesaje',
        'resource_label' => 'Mesaj',
        'resource_label_plural' => 'Mesaje',
        'status' => 'Status livrare',
        'opened_at' => 'Deschis la',
        'error' => 'Mesaj eroare',
        'actions' => [
            'hold' => 'Suspenda',
            'release' => 'Eliberează în coadă',
        ],
    ],
    'subscriptions' => [
        'title' => 'Abonamente',
        'resource_label' => 'Abonament',
        'resource_label_plural' => 'Abonamente',
    ],
    'settings' => [
        'title' => 'Setări notificări',
        'fields' => [
            'pwa_deliver_notifications' => [
                'label' => 'Livrare notificări PWA',
                'description' => 'Când dezactivat, notificările vor rămâne în coada din fundal și nu se vor încerca livrări către serviciile push.',
            ],
        ],
    ],
    'nav' => [
        'group' => 'Gestionare PWA',
    ],
];
