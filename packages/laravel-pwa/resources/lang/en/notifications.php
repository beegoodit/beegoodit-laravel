<?php

return [
    'title' => 'Push Notifications',
    'broadcasts' => [
        'title' => 'Broadcasts',
        'resource_label' => 'Broadcast',
        'resource_label_plural' => 'Broadcasts',
        'trigger_type' => 'Trigger Type',
        'status' => 'Status',
        'total_recipients' => 'Recipients',
        'total_sent' => 'Sent',
        'total_opened' => 'Opened',
        'content' => 'Content',
        'created_at' => 'Created At',
        'stats' => 'Statistics',
    ],
    'messages' => [
        'title' => 'Messages',
        'resource_label' => 'Message',
        'resource_label_plural' => 'Messages',
        'status' => 'Delivery Status',
        'opened_at' => 'Opened At',
        'error' => 'Error Message',
        'actions' => [
            'hold' => 'Set on Hold',
            'release' => 'Release to Queue',
        ],
    ],
    'subscriptions' => [
        'title' => 'Subscriptions',
        'resource_label' => 'Subscription',
        'resource_label_plural' => 'Subscriptions',
    ],
    'settings' => [
        'title' => 'Notification Settings',
        'fields' => [
            'pwa_deliver_notifications' => [
                'label' => 'Deliver PWA Notifications',
                'description' => 'When disabled, notifications will be held in the background queue and no delivery attempts will be made to push services.',
            ],
        ],
    ],
    'nav' => [
        'group' => 'PWA Management',
    ],
];
