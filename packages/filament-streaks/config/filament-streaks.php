<?php

return [

    'user_model' => \Illuminate\Foundation\Auth\User::class,

    'notification_channels' => ['mail'],

    'admin_event_models' => [],

    'event_title_attributes' => [],

    'scope_title_attributes' => [],

    'subject_title_attributes' => [],

    'strategies' => [
        // 'play_week' => [
        //     'strategy' => \App\Strategies\Streaks\PlayWeekStrategy::class,
        //     'open_on' => 'first_event',
        //     'active_from' => null,
        //     'active_to' => null,
        //     'when_inactive' => 'break',
        //     'at_risk' => [
        //         'notifications' => true,
        //         'lead' => '24h',
        //     ],
        //     'breaks' => [
        //         'grace_period' => '2d',
        //         'notifications' => true,
        //         'notification_delay' => '5m',
        //     ],
        //     'extended' => [
        //         'notifications' => false,
        //     ],
        //     'ui' => [
        //         'icon' => 'heroicon-o-fire',   // optional host presentation
        //         'color' => 'warning',          // Filament color name
        //         'label' => 'player.streaks.play_week', // optional lang key
        //     ],
        // ],
    ],

];
