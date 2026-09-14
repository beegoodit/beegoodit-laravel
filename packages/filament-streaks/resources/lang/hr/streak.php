<?php

return [
    'label' => 'Niz',
    'plural' => 'Nizovi',
    'navigation_group' => 'Nizovi',
    'overview' => [
        'navigation' => 'Pregled',
        'title' => 'Aktivni nizovi',
    ],
    'fields' => [
        'strategy' => 'Strategija',
        'subject' => 'Korisnik',
        'scope' => 'Opseg',
        'start_at' => 'Započeto',
        'end_at' => 'Završeno',
        'current' => 'Trenutno',
        'status' => 'Status',
        'progress' => 'Napredak',
    ],
    'status' => [
        'open' => 'Otvoren',
        'active' => 'Aktivan',
        'at_risk' => 'U riziku',
    ],
    'validation' => [
        'open_run_exists' => 'Već postoji otvoren niz za ovog korisnika, strategiju i opseg.',
    ],
];
