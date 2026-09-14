<?php

return [
    'label' => 'History-Lauf',
    'plural' => 'History',
    'navigation_group' => 'Serien',
    'fields' => [
        'from' => 'Von',
        'to' => 'Bis',
        'status' => 'Status',
        'requested_by' => 'Angefordert von',
        'started_at' => 'Gestartet',
        'finished_at' => 'Beendet',
        'created_at' => 'Erstellt',
        'result' => 'Ergebnis',
        'error' => 'Fehler',
    ],
    'status' => [
        'queued' => 'In Warteschlange',
        'running' => 'Läuft',
        'succeeded' => 'Erfolgreich',
        'failed' => 'Fehlgeschlagen',
    ],
    'notifications' => [
        'queued_title' => 'History-Replay in Warteschlange',
        'queued_body' => 'Du erhältst eine Benachrichtigung, wenn der Lauf fertig ist.',
        'succeeded_title' => 'History-Replay fertig',
        'succeeded_body' => 'Serien von :from bis :to neu berechnet.',
        'failed_title' => 'History-Replay fehlgeschlagen',
        'failed_body' => ':error',
        'view_action' => 'Lauf anzeigen',
    ],
];
