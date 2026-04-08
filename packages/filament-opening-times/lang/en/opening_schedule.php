<?php

return [
    'name' => 'Opening schedule',
    'plural' => 'Opening schedules',
    'navigation_group' => 'Opening hours',
    'relation_title' => 'Opening hours',
    'openable_label' => 'Location',
    'timezone_label' => 'Timezone',
    'active_from_label' => 'Active from',
    'active_to_label' => 'Active to',
    'closed_label' => 'Closed',
    'intervals_label' => 'Hours',
    'opens_at_label' => 'Opens at',
    'closes_at_label' => 'Closes at',
    'add_interval' => 'Add hours',
    'overnight_hint' => 'Closing time is before opening time on the same calendar day — this interval continues after midnight.',
    'validation' => [
        'overlapping_active_window' => 'This active period overlaps another schedule for the same place.',
    ],
];
