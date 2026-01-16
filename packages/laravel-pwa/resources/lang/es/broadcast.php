<?php

return [
    'navigation_label' => 'Difusión de Push',
    'heading' => 'Notificación de Difusión',
    'description' => 'Enviar una notificación push a usuarios específicos o a todos los usuarios suscritos.',
    'fields' => [
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
    ],
    'buttons' => [
        'send' => 'Enviar notificación',
    ],
    'notifications' => [
        'success' => [
            'title' => 'Push enviado',
            'body' => 'Notificación push enviada con éxito a :count suscripciones.',
        ],
    ],
];
