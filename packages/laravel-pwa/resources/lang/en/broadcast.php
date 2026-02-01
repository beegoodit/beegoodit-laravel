<?php

return [
    'navigation_label' => 'Push Broadcast',
    'heading' => 'Broadcast Notification',
    'description' => 'Send a push notification to specific or all subscribed users.',
    'resource' => [
        'label' => 'Broadcast',
        'plural_label' => 'Broadcasts',
    ],
    'fields' => [
        'status' => [
            'label' => 'Status',
            'options' => [
                'pending' => 'Pending',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'failed' => 'Failed',
                'sent' => 'Sent',
            ],
        ],
        'total_recipients' => [
            'label' => 'Recipients',
        ],
        'total_sent' => [
            'label' => 'Sent',
        ],
        'total_opened' => [
            'label' => 'Opened',
        ],
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
        'user' => [
            'label' => 'User',
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
        'created_at' => [
            'label' => 'Sent At',
        ],
        'opened_at' => [
            'label' => 'Opened At',
        ],
        'error_message' => [
            'label' => 'Error Message',
        ],
        'recipient' => [
            'label' => 'Recipient',
        ],
        'broadcast' => [
            'label' => 'Broadcast',
        ],
        'broadcast_id' => [
            'label' => 'Broadcast ID',
        ],
        'push_subscription_id' => [
            'label' => 'Subscription ID',
        ],
    ],
    'buttons' => [
        'send' => 'Send Notification',
        'resend' => 'Resend',
    ],
    'notifications' => [
        'success' => [
            'title' => 'Push Scheduled',
            'body' => 'Successfully scheduled push notification broadcast.',
        ],
        'requeued' => [
            'title' => 'Messages Re-queued',
            'body' => 'The messages have been added back to the delivery queue.',
        ],
        'new_tournament' => [
            'title' => '🏆 New Tournament',
            'body' => ':name on :date!',
        ],
        'results_updated' => [
            'title' => '📊 Results are in',
            'body' => 'Results are in for :name! Check your ranking now.',
        ],
    ],
];
