<?php

return [
    'title' => 'Notificaciones Push',
    'broadcasts' => [
        'title' => 'Difusiones',
        'resource_label' => 'Difusión',
        'resource_label_plural' => 'Difusiones',
        'trigger_type' => 'Tipo de activador',
        'status' => 'Estado',
        'total_recipients' => 'Destinatarios',
        'total_sent' => 'Enviados',
        'total_opened' => 'Abiertos',
        'payload' => 'Carga útil',
        'created_at' => 'Creado en',
        'stats' => 'Estadísticas',
    ],
    'messages' => [
        'title' => 'Mensajes',
        'status' => 'Estado del mensaje',
        'opened_at' => 'Abierto en',
        'error' => 'Mensaje de error',
    ],
    'subscriptions' => [
        'title' => 'Suscripciones',
        'resource_label' => 'Suscripción',
        'resource_label_plural' => 'Suscripciones',
    ],
    'settings' => [
        'title' => 'Ajustes de Notificación',
        'fields' => [
            'pwa_deliver_notifications' => [
                'label' => 'Entregar Notificaciones PWA',
                'description' => 'Cuando está desactivado, las notificaciones se mantendrán en la cola de fondo y no se realizarán intentos de entrega a los servicios push.',
            ],
        ],
    ],
    'nav' => [
        'group' => 'Gestión de PWA',
    ],
];
