<?php

return [
    'label' => 'Obavijest o nizu',
    'plural' => 'Obavijesti o nizovima',
    'navigation_group' => 'Nizovi',
    'fields' => [
        'streak' => 'Niz',
        'kind' => 'Vrsta',
        'period_key' => 'Razdoblje',
        'sent_at' => 'Poslano',
    ],
    'kinds' => [
        'at_risk' => 'U opasnosti',
        'broken' => 'Prekinut',
    ],
    'mail' => [
        'at_risk_subject' => 'Tvoj niz je u opasnosti',
        'at_risk_body' => 'Tvoj :strategy niz za razdoblje :period još nema kvalificirajući događaj.',
        'broken_subject' => 'Tvoj niz je prekinut',
        'broken_body' => 'Tvoj :strategy niz za razdoblje :period završio je bez kvalificirajućeg događaja.',
    ],
    'push' => [
        'at_risk_title' => 'Niz u opasnosti',
        'at_risk_body' => 'Tvoj :strategy niz za :period treba partiju ovaj tjedan.',
        'broken_title' => 'Niz prekinut',
        'broken_body' => 'Tvoj :strategy niz za :period je završio.',
    ],
];
