<?php

return [
    'label' => 'Kvalifikacija',
    'plural' => 'Kvalifikacije',
    'navigation_group' => 'Nizovi',
    'fields' => [
        'streak' => 'Niz',
        'event' => 'Događaj',
        'event_type' => 'Vrsta događaja',
        'event_id' => 'ID događaja',
        'happened_at' => 'Vrijeme',
    ],
    'validation' => [
        'outside_activity_window' => 'Vrijeme događaja je izvan prozora aktivnosti ove strategije niza.',
    ],
];
