<?php

return [
    'label' => 'Qualification',
    'plural' => 'Qualifications',
    'navigation_group' => 'Streaks',
    'fields' => [
        'streak' => 'Streak',
        'event' => 'Event',
        'event_type' => 'Event type',
        'event_id' => 'Event ID',
        'happened_at' => 'Happened at',
    ],
    'validation' => [
        'outside_activity_window' => 'The event time is outside this streak strategy activity window.',
    ],
];
