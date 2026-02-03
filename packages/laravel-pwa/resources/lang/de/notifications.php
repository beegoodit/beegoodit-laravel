<?php

return [
    'title' => 'Push-Benachrichtigungen',
    'broadcasts' => [
        'title' => 'Broadcasts',
        'resource_label' => 'Broadcast',
        'resource_label_plural' => 'Broadcasts',
        'trigger_type' => 'Auslöser',
        'status' => 'Status',
        'total_recipients' => 'Empfänger',
        'total_sent' => 'Gesendet',
        'total_opened' => 'Geöffnet',
        'content' => 'Inhalt',
        'created_at' => 'Erstellt am',
        'stats' => 'Statistiken',
    ],
    'messages' => [
        'title' => 'Nachrichten',
        'resource_label' => 'Nachricht',
        'resource_label_plural' => 'Nachrichten',
        'status' => 'Nachrichtenstatus',
        'opened_at' => 'Geöffnet am',
        'error' => 'Fehlermeldung',
    ],
    'subscriptions' => [
        'title' => 'Abonnements',
        'resource_label' => 'Abonnement',
        'resource_label_plural' => 'Abonnements',
    ],
    'settings' => [
        'title' => 'Benachrichtigungs-Einstellungen',
        'fields' => [
            'pwa_deliver_notifications' => [
                'label' => 'PWA-Benachrichtigungen zustellen',
                'description' => 'Wenn deaktiviert, werden Benachrichtigungen in der Hintergrund-Warteschlange gehalten und es werden keine Zustellversuche an Push-Dienste unternommen.',
            ],
        ],
    ],
    'nav' => [
        'group' => 'PWA-Management',
    ],
];
