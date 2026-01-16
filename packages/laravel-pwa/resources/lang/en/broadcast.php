<?php

return [
    'navigation_label' => 'Push Broadcast',
    'heading' => 'Broadcast Notification',
    'description' => 'Send a push notification to specific or all subscribed users.',
    'fields' => [
        'target_type' => [
            'label' => 'Target',
            'options' => [
                'all' => 'All Users',
                'users' => 'Specific Users',
            ],
        ],
        'users' => [
            'label' => 'Users',
        ],
        'title' => [
            'label' => 'Title',
            'placeholder' => 'e.g. New Event Published!',
        ],
        'body' => [
            'label' => 'Body',
            'placeholder' => 'e.g. A new tournament is available in your city.',
        ],
        'action_url' => [
            'label' => 'Action URL (optional)',
            'placeholder' => 'https://...',
        ],
    ],
    'buttons' => [
        'send' => 'Send Notification',
    ],
    'notifications' => [
        'success' => [
            'title' => 'Push Sent',
            'body' => 'Successfully sent push notification to :count subscriptions.',
        ],
    ],
];
