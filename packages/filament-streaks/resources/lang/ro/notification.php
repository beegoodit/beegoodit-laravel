<?php

return [
    'label' => 'Notificare serie',
    'plural' => 'Notificări serie',
    'navigation_group' => 'Serii',
    'fields' => [
        'streak' => 'Serie',
        'kind' => 'Tip',
        'period_key' => 'Perioadă',
        'sent_at' => 'Trimisă',
    ],
    'kinds' => [
        'at_risk' => 'În risc',
        'broken' => 'Întreruptă',
    ],
    'mail' => [
        'at_risk_subject' => 'Seria ta este în risc',
        'at_risk_body' => 'Seria ta :strategy pentru perioada :period nu are încă un eveniment calificator.',
        'broken_subject' => 'Seria ta a fost întreruptă',
        'broken_body' => 'Seria ta :strategy pentru perioada :period s-a încheiat fără un eveniment calificator.',
    ],
    'push' => [
        'at_risk_title' => 'Serie în risc',
        'at_risk_body' => 'Seria ta :strategy pentru :period are nevoie de un joc săptămâna aceasta.',
        'broken_title' => 'Serie întreruptă',
        'broken_body' => 'Seria ta :strategy pentru :period s-a încheiat.',
    ],
];
