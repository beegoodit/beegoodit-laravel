<?php

return [
    'label' => 'Calificare',
    'plural' => 'Calificări',
    'navigation_group' => 'Serii',
    'fields' => [
        'streak' => 'Serie',
        'event' => 'Eveniment',
        'event_type' => 'Tip eveniment',
        'event_id' => 'ID eveniment',
        'happened_at' => 'Data și ora',
    ],
    'validation' => [
        'outside_activity_window' => 'Momentul evenimentului este în afara ferestrei de activitate a acestei strategii de serie.',
    ],
];
