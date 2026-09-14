<?php

return [
    'label' => 'History run',
    'plural' => 'History',
    'navigation_group' => 'Streaks',
    'fields' => [
        'from' => 'From',
        'to' => 'To',
        'status' => 'Status',
        'requested_by' => 'Requested by',
        'started_at' => 'Started',
        'finished_at' => 'Finished',
        'created_at' => 'Created',
        'result' => 'Result',
        'error' => 'Error',
    ],
    'status' => [
        'queued' => 'Queued',
        'running' => 'Running',
        'succeeded' => 'Succeeded',
        'failed' => 'Failed',
    ],
    'notifications' => [
        'queued_title' => 'History replay queued',
        'queued_body' => 'You will get a notification when it finishes.',
        'succeeded_title' => 'History replay finished',
        'succeeded_body' => 'Replayed streaks from :from to :to.',
        'failed_title' => 'History replay failed',
        'failed_body' => ':error',
        'view_action' => 'View run',
    ],
];
