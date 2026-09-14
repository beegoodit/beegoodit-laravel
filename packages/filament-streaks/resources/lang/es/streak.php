<?php

return [
    'label' => 'Racha',
    'plural' => 'Rachas',
    'navigation_group' => 'Rachas',
    'overview' => [
        'navigation' => 'Resumen',
        'title' => 'Rachas activas',
    ],
    'fields' => [
        'strategy' => 'Estrategia',
        'subject' => 'Usuario',
        'scope' => 'Ámbito',
        'start_at' => 'Iniciada',
        'end_at' => 'Finalizada',
        'current' => 'Actual',
        'status' => 'Estado',
        'progress' => 'Progreso',
    ],
    'status' => [
        'open' => 'Abierta',
        'active' => 'Activa',
        'at_risk' => 'En riesgo',
    ],
    'validation' => [
        'open_run_exists' => 'Ya existe una racha abierta para este usuario, estrategia y ámbito.',
    ],
];
