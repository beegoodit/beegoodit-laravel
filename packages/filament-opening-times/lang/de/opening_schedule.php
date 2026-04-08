<?php

return [
    'name' => 'Öffnungszeiten-Plan',
    'plural' => 'Öffnungszeiten-Pläne',
    'navigation_group' => 'Öffnungszeiten',
    'relation_title' => 'Öffnungszeiten',
    'openable_label' => 'Standort',
    'timezone_label' => 'Zeitzone',
    'active_from_label' => 'Aktiv von',
    'active_to_label' => 'Aktiv bis',
    'closed_label' => 'Geschlossen',
    'intervals_label' => 'Zeiten',
    'opens_at_label' => 'Öffnet um',
    'closes_at_label' => 'Schließt um',
    'add_interval' => 'Zeiten hinzufügen',
    'overnight_hint' => 'Die Schließzeit liegt vor der Öffnungszeit am selben Kalendertag — dieser Zeitraum geht über Mitternacht hinaus.',
    'validation' => [
        'overlapping_active_window' => 'Dieser aktive Zeitraum überschneidet sich mit einem anderen Plan für denselben Ort.',
    ],
];
