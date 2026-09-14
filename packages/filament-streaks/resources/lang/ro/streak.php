<?php

return [
    'label' => 'Serie',
    'plural' => 'Serii',
    'navigation_group' => 'Serii',
    'overview' => [
        'navigation' => 'Prezentare generală',
        'title' => 'Serii active',
    ],
    'fields' => [
        'strategy' => 'Strategie',
        'subject' => 'Utilizator',
        'scope' => 'Domeniu',
        'start_at' => 'Început',
        'end_at' => 'Încheiată',
        'current' => 'Curent',
        'status' => 'Stare',
        'progress' => 'Progres',
    ],
    'status' => [
        'open' => 'Deschisă',
        'active' => 'Activă',
        'at_risk' => 'În pericol',
    ],
    'validation' => [
        'open_run_exists' => 'Există deja o serie deschisă pentru acest utilizator, strategie și domeniu.',
    ],
];
