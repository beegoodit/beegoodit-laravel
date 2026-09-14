<?php

return [
    'label' => 'Qualifikation',
    'plural' => 'Qualifikationen',
    'navigation_group' => 'Serien',
    'fields' => [
        'streak' => 'Serie',
        'event' => 'Ereignis',
        'event_type' => 'Ereignistyp',
        'event_id' => 'Ereignis-ID',
        'happened_at' => 'Zeitpunkt',
    ],
    'validation' => [
        'outside_activity_window' => 'Der Zeitpunkt liegt außerhalb des Aktivitätsfensters dieser Serien-Strategie.',
    ],
];
