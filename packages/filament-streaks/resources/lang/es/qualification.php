<?php

return [
    'label' => 'Calificación',
    'plural' => 'Calificaciones',
    'navigation_group' => 'Rachas',
    'fields' => [
        'streak' => 'Racha',
        'event' => 'Evento',
        'event_type' => 'Tipo de evento',
        'event_id' => 'ID del evento',
        'happened_at' => 'Fecha y hora',
    ],
    'validation' => [
        'outside_activity_window' => 'La fecha del evento está fuera de la ventana de actividad de esta estrategia de racha.',
    ],
];
