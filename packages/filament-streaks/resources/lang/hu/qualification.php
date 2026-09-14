<?php

return [
    'label' => 'Kvalifikáció',
    'plural' => 'Kvalifikációk',
    'navigation_group' => 'Sorozatok',
    'fields' => [
        'streak' => 'Sorozat',
        'event' => 'Esemény',
        'event_type' => 'Eseménytípus',
        'event_id' => 'Eseményazonosító',
        'happened_at' => 'Időpont',
    ],
    'validation' => [
        'outside_activity_window' => 'Az esemény időpontja kívül esik ennek a sorozat-stratégiának az aktivitási ablakán.',
    ],
];
