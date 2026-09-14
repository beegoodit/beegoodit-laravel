<?php

return [
    'label' => 'Streak',
    'plural' => 'Streaks',
    'navigation_group' => 'Streaks',
    'overview' => [
        'navigation' => 'Overview',
        'title' => 'Running streaks',
    ],
    'fields' => [
        'strategy' => 'Strategy',
        'subject' => 'Subject',
        'scope' => 'Scope',
        'start_at' => 'Started',
        'end_at' => 'Ended',
        'current' => 'Current',
        'status' => 'Status',
        'progress' => 'Progress',
    ],
    'status' => [
        'open' => 'Open',
        'active' => 'Active',
        'at_risk' => 'At risk',
    ],
    'validation' => [
        'open_run_exists' => 'An open streak already exists for this subject, strategy, and scope.',
    ],
];
