<?php

return [
    'navigation_label' => 'Push Broadcast',
    'heading' => 'Notificare broadcast',
    'description' => 'Trimite o notificare push către utilizatori specifici sau toți abonații.',
    'resource' => [
        'label' => 'Broadcast',
        'plural_label' => 'Broadcast-uri',
    ],
    'fields' => [
        'status' => [
            'label' => 'Status',
            'options' => [
                'pending' => 'În așteptare',
                'on_hold' => 'Suspendat',
                'processing' => 'În curs',
                'completed' => 'Finalizat',
                'failed' => 'Eșuat',
                'sent' => 'Trimis',
            ],
        ],
        'total_recipients' => ['label' => 'Destinatari'],
        'total_sent' => ['label' => 'Trimise'],
        'total_opened' => ['label' => 'Deschise'],
        'target_type' => [
            'label' => 'Țintă',
            'options' => [
                'all' => 'Toți utilizatorii',
                'users' => 'Utilizatori specifici',
            ],
        ],
        'users' => ['label' => 'Utilizatori'],
        'user' => ['label' => 'Utilizator'],
        'title' => [
            'label' => 'Titlu',
            'placeholder' => 'ex. Eveniment nou publicat!',
        ],
        'body' => [
            'label' => 'Conținut',
            'placeholder' => 'ex. Un turneu nou este disponibil în orașul tău.',
        ],
        'action_url' => [
            'label' => 'URL acțiune',
            'placeholder' => 'https://...',
        ],
        'created_at' => ['label' => 'Trimis la'],
        'opened_at' => ['label' => 'Deschis la'],
        'error_message' => ['label' => 'Mesaj eroare'],
        'recipient' => ['label' => 'Destinatar'],
        'broadcast' => ['label' => 'Broadcast'],
        'broadcast_id' => ['label' => 'ID Broadcast'],
        'push_subscription_id' => ['label' => 'ID abonament'],
        'endpoint' => ['label' => 'Endpoint'],
        'encoding' => ['label' => 'Codare'],
    ],
    'buttons' => [
        'send' => 'Trimite notificare',
        'resend' => 'Retrimite',
        'view' => 'Vizualizează',
        'test_notification' => 'Trimite notificare test',
    ],
    'notifications' => [
        'success' => [
            'title' => 'Push programat',
            'body' => 'Broadcast-ul push a fost programat cu succes.',
        ],
        'requeued' => [
            'title' => 'Mesaje re-adaugate în coadă',
            'body' => 'Mesajele au fost readăugate în coada de livrare.',
        ],
        'test_sent' => [
            'title' => 'Notificare test trimisă',
            'body' => 'Notificarea test a fost trimisă către serviciul push.',
        ],
        'test_failed' => [
            'title' => 'Notificare test eșuată',
            'body' => 'Trimiterea notificării test a eșuat. Verifică logurile pentru detalii.',
        ],
        'held' => [
            'title' => 'Procesare suspendată',
            'body' => 'Toate mesajele în așteptare pentru acest broadcast au fost suspendate.',
        ],
        'released' => [
            'title' => 'Procesare reluată',
            'body' => 'Toate mesajele suspendate au fost eliberate în coada de livrare.',
        ],
        'new_tournament' => [
            'title' => '🏆 Turneu nou',
            'body' => ':name pe :date!',
        ],
        'results_updated' => [
            'title' => '📊 Rezultate disponibile',
            'body' => 'Rezultatele pentru :name sunt disponibile! Verifică clasamentul acum.',
        ],
    ],
];
