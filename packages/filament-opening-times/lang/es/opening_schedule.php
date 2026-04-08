<?php

return [
    'name' => 'Horario de apertura',
    'plural' => 'Horarios de apertura',
    'navigation_group' => 'Horarios',
    'relation_title' => 'Horario de apertura',
    'openable_label' => 'Ubicación',
    'timezone_label' => 'Zona horaria',
    'active_from_label' => 'Activo desde',
    'active_to_label' => 'Activo hasta',
    'closed_label' => 'Cerrado',
    'intervals_label' => 'Franjas',
    'opens_at_label' => 'Abre a las',
    'closes_at_label' => 'Cierra a las',
    'add_interval' => 'Añadir franja',
    'overnight_hint' => 'La hora de cierre es anterior a la de apertura el mismo día calendario: este tramo continúa después de medianoche.',
    'validation' => [
        'overlapping_active_window' => 'Este periodo activo se solapa con otro horario del mismo lugar.',
    ],
];
