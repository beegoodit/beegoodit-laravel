<?php

return [
    'label' => 'Streak notification',
    'plural' => 'Streak notifications',
    'navigation_group' => 'Streaks',
    'fields' => [
        'streak' => 'Streak',
        'kind' => 'Kind',
        'period_key' => 'Period',
        'sent_at' => 'Sent at',
    ],
    'kinds' => [
        'at_risk' => 'At risk',
        'broken' => 'Broken',
    ],
    'mail' => [
        'at_risk_subject' => 'Your streak is at risk',
        'at_risk_body' => 'Your :strategy streak for period :period has no qualifying event yet.',
        'broken_subject' => 'Your streak was broken',
        'broken_body' => 'Your :strategy streak for period :period ended without a qualifying event.',
    ],
    'push' => [
        'at_risk_title' => 'Streak at risk',
        'at_risk_body' => 'Your :strategy streak for :period needs a play this week.',
        'broken_title' => 'Streak broken',
        'broken_body' => 'Your :strategy streak for :period has ended.',
    ],
];
