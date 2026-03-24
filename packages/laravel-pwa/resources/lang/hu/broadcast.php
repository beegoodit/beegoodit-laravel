<?php

return [
    'navigation_label' => 'Push Broadcast',
    'heading' => 'Broadcast értesítés',
    'description' => 'Push értesítés küldése meghatározott vagy minden feliratkozó felhasználónak.',
    'resource' => [
        'label' => 'Broadcast',
        'plural_label' => 'Broadcastok',
    ],
    'fields' => [
        'status' => [
            'label' => 'Státusz',
            'options' => [
                'pending' => 'Folyamatban',
                'on_hold' => 'Várakoztatva',
                'processing' => 'Feldolgozás',
                'completed' => 'Kész',
                'failed' => 'Sikertelen',
                'sent' => 'Elküldve',
            ],
        ],
        'total_recipients' => ['label' => 'Címzettek'],
        'total_sent' => ['label' => 'Elküldve'],
        'total_opened' => ['label' => 'Megnyitva'],
        'target_type' => [
            'label' => 'Cél',
            'options' => [
                'all' => 'Minden felhasználó',
                'users' => 'Meghatározott felhasználók',
            ],
        ],
        'users' => ['label' => 'Felhasználók'],
        'user' => ['label' => 'Felhasználó'],
        'title' => [
            'label' => 'Cím',
            'placeholder' => 'pl. Új esemény közzétéve!',
        ],
        'body' => [
            'label' => 'Tartalom',
            'placeholder' => 'pl. Új turné érhető el a városában.',
        ],
        'action_url' => [
            'label' => 'Művelet URL',
            'placeholder' => 'https://...',
        ],
        'created_at' => ['label' => 'Elküldve'],
        'opened_at' => ['label' => 'Megnyitva'],
        'error_message' => ['label' => 'Hibaüzenet'],
        'recipient' => ['label' => 'Címzett'],
        'broadcast' => ['label' => 'Broadcast'],
        'broadcast_id' => ['label' => 'Broadcast ID'],
        'push_subscription_id' => ['label' => 'Feliratkozás ID'],
        'endpoint' => ['label' => 'Végpont'],
        'encoding' => ['label' => 'Kódolás'],
    ],
    'buttons' => [
        'send' => 'Értesítés küldése',
        'resend' => 'Újraküldés',
        'view' => 'Megtekintés',
        'test_notification' => 'Teszt értesítés küldése',
    ],
    'notifications' => [
        'success' => [
            'title' => 'Push ütemezve',
            'body' => 'A push broadcast sikeresen ütemezve.',
        ],
        'requeued' => [
            'title' => 'Üzenetek visszarakva a sorba',
            'body' => 'Az üzenetek vissza kerültek a kézbesítési sorba.',
        ],
        'test_sent' => [
            'title' => 'Teszt értesítés elküldve',
            'body' => 'A teszt értesítés elküldve a push szolgáltatásnak.',
        ],
        'test_failed' => [
            'title' => 'Teszt értesítés sikertelen',
            'body' => 'A teszt értesítés küldése sikertelen. Ellenőrizze a naplókat.',
        ],
        'held' => [
            'title' => 'Feldolgozás szüneteltetve',
            'body' => 'A broadcast minden függőben lévő üzenete várakoztatásra került.',
        ],
        'released' => [
            'title' => 'Feldolgozás folytatva',
            'body' => 'A várakoztatott üzenetek vissza kerültek a kézbesítési sorba.',
        ],
        'new_tournament' => [
            'title' => '🏆 Új turné',
            'body' => ':name ekkor: :date!',
        ],
        'results_updated' => [
            'title' => '📊 Eredmények megérkeztek',
            'body' => 'Az eredmények megérkeztek: :name! Ellenőrizze a rangsort.',
        ],
    ],
];
