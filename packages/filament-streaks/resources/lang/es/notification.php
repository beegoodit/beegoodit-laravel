<?php

return [
    'label' => 'Notificación de racha',
    'plural' => 'Notificaciones de racha',
    'navigation_group' => 'Rachas',
    'fields' => [
        'streak' => 'Racha',
        'kind' => 'Tipo',
        'period_key' => 'Periodo',
        'sent_at' => 'Enviada',
    ],
    'kinds' => [
        'at_risk' => 'En riesgo',
        'broken' => 'Rota',
    ],
    'mail' => [
        'at_risk_subject' => 'Tu racha está en riesgo',
        'at_risk_body' => 'Tu racha :strategy del periodo :period aún no tiene un evento que califique.',
        'broken_subject' => 'Tu racha se ha roto',
        'broken_body' => 'Tu racha :strategy del periodo :period terminó sin un evento que califique.',
    ],
    'push' => [
        'at_risk_title' => 'Racha en riesgo',
        'at_risk_body' => 'Tu racha :strategy de :period necesita una partida esta semana.',
        'broken_title' => 'Racha rota',
        'broken_body' => 'Tu racha :strategy de :period ha terminado.',
    ],
];
