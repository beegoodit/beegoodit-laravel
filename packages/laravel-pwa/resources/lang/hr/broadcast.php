<?php

return [
    'navigation_label' => 'Push emitiranje',
    'heading' => 'Emitiraj obavijest',
    'description' => 'Pošaljite push obavijest odabranim ili svim pretplaćenim korisnicima.',
    'resource' => [
        'label' => 'Emitiranje',
        'plural_label' => 'Emitiranja',
    ],
    'fields' => [
        'status' => [
            'label' => 'Status',
            'options' => [
                'pending' => 'Na čekanju',
                'on_hold' => 'Na holdu',
                'processing' => 'Obrada',
                'completed' => 'Završeno',
                'failed' => 'Neuspjelo',
                'sent' => 'Poslano',
            ],
        ],
        'total_recipients' => [
            'label' => 'Primatelji',
        ],
        'total_sent' => [
            'label' => 'Poslano',
        ],
        'total_opened' => [
            'label' => 'Otvoreno',
        ],
        'target_type' => [
            'label' => 'Cilj',
            'options' => [
                'all' => 'Svi korisnici',
                'users' => 'Odabrani korisnici',
            ],
        ],
        'users' => [
            'label' => 'Korisnici',
        ],
        'user' => [
            'label' => 'Korisnik',
        ],
        'title' => [
            'label' => 'Naslov',
            'placeholder' => 'npr. Objavljen novi događaj!',
        ],
        'body' => [
            'label' => 'Sadržaj',
            'placeholder' => 'npr. Novi turnir dostupan je u vašem gradu.',
        ],
        'action_url' => [
            'label' => 'URL radnje',
            'placeholder' => 'https://...',
        ],
        'created_at' => [
            'label' => 'Poslano',
        ],
        'opened_at' => [
            'label' => 'Otvoreno',
        ],
        'error_message' => [
            'label' => 'Poruka o pogrešci',
        ],
        'recipient' => [
            'label' => 'Primatelj',
        ],
        'broadcast' => [
            'label' => 'Emitiranje',
        ],
        'broadcast_id' => [
            'label' => 'ID emitiranja',
        ],
        'push_subscription_id' => [
            'label' => 'ID pretplate',
        ],
        'endpoint' => [
            'label' => 'Endpoint',
        ],
        'encoding' => [
            'label' => 'Kodiranje',
        ],
    ],
    'buttons' => [
        'send' => 'Pošalji obavijest',
        'resend' => 'Pošalji ponovno',
        'view' => 'Prikaži',
        'test_notification' => 'Pošalji testnu obavijest',
    ],
    'notifications' => [
        'success' => [
            'title' => 'Push zakazan',
            'body' => 'Emitiranje push obavijesti uspješno je zakazano.',
        ],
        'requeued' => [
            'title' => 'Poruke ponovno u redu',
            'body' => 'Poruke su vraćene u red za isporuku.',
        ],
        'test_sent' => [
            'title' => 'Testna obavijest poslana',
            'body' => 'Testna obavijest poslana je push usluzi.',
        ],
        'test_failed' => [
            'title' => 'Testna obavijest nije uspjela',
            'body' => 'Slanje testne obavijesti nije uspjelo. Provjerite zapisnike.',
        ],
        'held' => [
            'title' => 'Obrada pauzirana',
            'body' => 'Sve poruke na čekanju za ovo emitiranje stavljene su na hold.',
        ],
        'released' => [
            'title' => 'Obrada nastavljena',
            'body' => 'Sve zadržane poruke za ovo emitiranje vraćene su u red za isporuku.',
        ],
        'new_tournament' => [
            'title' => '🏆 Novi turnir',
            'body' => ':name :date!',
        ],
        'results_updated' => [
            'title' => '📊 Rezultati su objavljeni',
            'body' => 'Rezultati za :name su objavljeni! Provjerite svoj plasman.',
        ],
    ],
];
