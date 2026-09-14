<?php

return [
    'label' => 'Serie',
    'plural' => 'Serien',
    'navigation_group' => 'Serien',
    'overview' => [
        'navigation' => 'Übersicht',
        'title' => 'Laufende Serien',
    ],
    'fields' => [
        'strategy' => 'Strategie',
        'subject' => 'Nutzer',
        'scope' => 'Gültigkeitsbereich',
        'start_at' => 'Gestartet am',
        'end_at' => 'Beendet am',
        'current' => 'Aktuell',
        'status' => 'Status',
        'progress' => 'Fortschritt',
    ],
    'status' => [
        'open' => 'Offen',
        'active' => 'Aktiv',
        'at_risk' => 'Gefährdet',
    ],
    'validation' => [
        'open_run_exists' => 'Für diesen Nutzer, diese Strategie und diesen Gültigkeitsbereich existiert bereits eine offene Serie.',
    ],
];
