<?php

return [
    'navigation_label' => 'Difusión de Push',
    'heading' => 'Notificación de Difusión',
    'description' => 'Enviar una notificación push a usuarios específicos o a todos los usuarios suscritos.',
    'resource' => [
        'label' => 'Difusión',
        'plural_label' => 'Difusiones',
    ],
    'fields' => [
        'status' => [
            'label' => 'Estado',
            'options' => [
                'pending' => 'Pendiente',
                'processing' => 'Procesando',
                'completed' => 'Completado',
                'failed' => 'Fallido',
                'sent' => 'Enviado',
            ],
        ],
        'total_recipients' => [
            'label' => 'Destinatarios',
        ],
        'total_sent' => [
            'label' => 'Enviados',
        ],
        'total_opened' => [
            'label' => 'Abiertos',
        ],
        'target_type' => [
            'label' => 'Objetivo',
            'options' => [
                'all' => 'Todos los usuarios',
                'users' => 'Usuarios específicos',
            ],
        ],
        'users' => [
            'label' => 'Usuarios',
        ],
        'user' => [
            'label' => 'Usuario',
        ],
        'title' => [
            'label' => 'Título',
            'placeholder' => 'p.ej. ¡Nuevo evento publicado!',
        ],
        'body' => [
            'label' => 'Cuerpo',
            'placeholder' => 'p.ej. Un nuevo torneo está disponible en tu ciudad.',
        ],
        'action_url' => [
            'label' => 'URL de acción (opcional)',
            'placeholder' => 'https://...',
        ],
        'created_at' => [
            'label' => 'Enviado el',
        ],
        'opened_at' => [
            'label' => 'Abierto el',
        ],
        'error_message' => [
            'label' => 'Mensaje de error',
        ],
        'recipient' => [
            'label' => 'Destinatario',
        ],
        'broadcast' => [
            'label' => 'Difusión',
        ],
        'broadcast_id' => [
            'label' => 'ID de Difusión',
        ],
        'push_subscription_id' => [
            'label' => 'ID de Suscripción',
        ],
    ],
    'buttons' => [
        'send' => 'Enviar notificación',
        'resend' => 'Reenviar',
    ],
    'notifications' => [
        'success' => [
            'title' => 'Push programado',
            'body' => 'Notificación push programada con éxito.',
        ],
        'requeued' => [
            'title' => 'Cola actualizada',
            'body' => 'Las notificaciones han sido puestas en cola nuevamente.',
        ],
        'new_tournament' => [
            'title' => '🏆 Nuevo Torneo',
            'body' => '¡:name el :date!',
        ],
        'results_updated' => [
            'title' => '📊 Resultados disponibles',
            'body' => '¡Los resultados de :name ya están disponibles! Consulta tu clasificación ahora.',
        ],
    ],
];
