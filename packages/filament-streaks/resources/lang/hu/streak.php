<?php

return [
    'label' => 'Sorozat',
    'plural' => 'Sorozatok',
    'navigation_group' => 'Sorozatok',
    'overview' => [
        'navigation' => 'Áttekintés',
        'title' => 'Futó sorozatok',
    ],
    'fields' => [
        'strategy' => 'Stratégia',
        'subject' => 'Felhasználó',
        'scope' => 'Hatókör',
        'start_at' => 'Kezdés',
        'end_at' => 'Befejezve',
        'current' => 'Jelenlegi',
        'status' => 'Állapot',
        'progress' => 'Haladás',
    ],
    'status' => [
        'open' => 'Nyitott',
        'active' => 'Aktív',
        'at_risk' => 'Veszélyben',
    ],
    'validation' => [
        'open_run_exists' => 'Ehhez a felhasználóhoz, stratégiához és hatókörhöz már létezik nyitott sorozat.',
    ],
];
