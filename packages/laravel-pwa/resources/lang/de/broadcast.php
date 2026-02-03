<?php

return [
    'navigation_label' => 'Push-Broadcast',
    'heading' => 'Broadcast-Benachrichtigung',
    'description' => 'Sende eine Push-Benachrichtigung an bestimmte oder alle abonnierten Benutzer.',
    'resource' => [
        'label' => 'Broadcast',
        'plural_label' => 'Broadcasts',
    ],
    'fields' => [
        'status' => [
            'label' => 'Status',
            'options' => [
                'pending' => 'Wartend',
                'processing' => 'Verarbeitung',
                'completed' => 'Abgeschlossen',
                'failed' => 'Fehlgeschlagen',
                'sent' => 'Gesendet',
            ],
        ],
        'total_recipients' => [
            'label' => 'Empfänger',
        ],
        'total_sent' => [
            'label' => 'Gesendet',
        ],
        'total_opened' => [
            'label' => 'Geöffnet',
        ],
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
        'user' => [
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
            'label' => 'Aktions-URL',
            'placeholder' => 'https://...',
        ],
        'created_at' => [
            'label' => 'Gesendet am',
        ],
        'opened_at' => [
            'label' => 'Geöffnet am',
        ],
        'error_message' => [
            'label' => 'Fehlermeldung',
        ],
        'recipient' => [
            'label' => 'Empfänger',
        ],
        'broadcast' => [
            'label' => 'Broadcast',
        ],
        'broadcast_id' => [
            'label' => 'Broadcast-ID',
        ],
        'push_subscription_id' => [
            'label' => 'Abonnement-ID',
        ],
        'endpoint' => [
            'label' => 'Endpunkt',
        ],
        'encoding' => [
            'label' => 'Kodierung',
        ],
    ],
    'buttons' => [
        'send' => 'Benachrichtigung senden',
        'resend' => 'Erneut senden',
        'view' => 'Anzeigen',
        'test_notification' => 'Test-Benachrichtigung senden',
    ],
    'notifications' => [
        'success' => [
            'title' => 'Push geplant',
            'body' => 'Push-Benachrichtigung wurde erfolgreich zur Übertragung geplant.',
        ],
        'requeued' => [
            'title' => 'Warteschlange aktualisiert',
            'body' => 'Benachrichtigungen wurden erneut in die Warteschlange gestellt.',
        ],
        'test_sent' => [
            'title' => 'Test-Benachrichtigung gesendet',
            'body' => 'Die Test-Benachrichtigung wurde an den Push-Dienst gesendet.',
        ],
        'test_failed' => [
            'title' => 'Test-Benachrichtigung fehlgeschlagen',
            'body' => 'Die Test-Benachrichtigung konnte nicht gesendet werden. Prüfen Sie die Web-Logs für Details.',
        ],
        'new_tournament' => [
            'title' => '🏆 Neues Turnier',
            'body' => ':name am :date!',
        ],
        'results_updated' => [
            'title' => '📊 Ergebnisse sind da',
            'body' => 'Ergebnisse für :name sind verfügbar! Schau dir jetzt dein Ranking an.',
        ],
    ],
];
