<?php

return [
    'title' => 'Push obavijesti',
    'broadcasts' => [
        'title' => 'Emitiranja',
        'resource_label' => 'Emitiranje',
        'resource_label_plural' => 'Emitiranja',
        'trigger_type' => 'Vrsta okidača',
        'status' => 'Status',
        'total_recipients' => 'Primatelji',
        'total_sent' => 'Poslano',
        'total_opened' => 'Otvoreno',
        'content' => 'Sadržaj',
        'created_at' => 'Stvoreno',
        'stats' => 'Statistika',
    ],
    'messages' => [
        'title' => 'Poruke',
        'resource_label' => 'Poruka',
        'resource_label_plural' => 'Poruke',
        'status' => 'Status isporuke',
        'opened_at' => 'Otvoreno',
        'error' => 'Poruka o pogrešci',
        'actions' => [
            'hold' => 'Stavi na čekanje',
            'release' => 'Vrati u red',
        ],
    ],
    'subscriptions' => [
        'title' => 'Pretplate',
        'resource_label' => 'Pretplata',
        'resource_label_plural' => 'Pretplate',
    ],
    'settings' => [
        'title' => 'Postavke obavijesti',
        'fields' => [
            'pwa_deliver_notifications' => [
                'label' => 'Dostavi PWA obavijesti',
                'description' => 'Kada je isključeno, obavijesti se zadržavaju u pozadinskom redu i ne šalju se push uslugama.',
            ],
        ],
    ],
    'nav' => [
        'group' => 'Upravljanje PWA-om',
    ],
];
