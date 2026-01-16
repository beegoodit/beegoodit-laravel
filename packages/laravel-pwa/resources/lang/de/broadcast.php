<?php

return [
    'navigation_label' => 'Push-Broadcast',
    'heading' => 'Broadcast-Benachrichtigung',
    'description' => 'Sende eine Push-Benachrichtigung an bestimmte oder alle abonnierten Benutzer.',
    'fields' => [
        'target_type' => [
            'label' => 'Zielgruppe',
            'options' => [
                'all' => 'Alle Benutzer',
                'users' => 'Bestimmte Benutzer',
            ],
        ],
        'users' => [
            'label' => 'Benutzer',
        ],
        'title' => [
            'label' => 'Titel',
            'placeholder' => 'z.B. Neues Event veröffentlicht!',
        ],
        'body' => [
            'label' => 'Inhalt',
            'placeholder' => 'z.B. Ein neues Turnier ist in deiner Stadt verfügbar.',
        ],
        'action_url' => [
            'label' => 'Aktions-URL (optional)',
            'placeholder' => 'https://...',
        ],
    ],
    'buttons' => [
        'send' => 'Benachrichtigung senden',
    ],
    'notifications' => [
        'success' => [
            'title' => 'Push gesendet',
            'body' => 'Push-Benachrichtigung erfolgreich an :count Abonnements gesendet.',
        ],
    ],
];
