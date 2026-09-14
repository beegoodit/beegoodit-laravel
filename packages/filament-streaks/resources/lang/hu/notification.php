<?php

return [
    'label' => 'Sorozat-értesítés',
    'plural' => 'Sorozat-értesítések',
    'navigation_group' => 'Sorozatok',
    'fields' => [
        'streak' => 'Sorozat',
        'kind' => 'Típus',
        'period_key' => 'Időszak',
        'sent_at' => 'Elküldve',
    ],
    'kinds' => [
        'at_risk' => 'Veszélyben',
        'broken' => 'Megszakítva',
    ],
    'mail' => [
        'at_risk_subject' => 'A sorozatod veszélyben van',
        'at_risk_body' => 'A :strategy sorozatodnak a :period időszakban még nincs kvalifikáló eseménye.',
        'broken_subject' => 'A sorozatod megszakadt',
        'broken_body' => 'A :strategy sorozatod a :period időszakban kvalifikáló esemény nélkül ért véget.',
    ],
    'push' => [
        'at_risk_title' => 'Sorozat veszélyben',
        'at_risk_body' => 'A :strategy sorozatod (:period) még játékot igényel ezen a héten.',
        'broken_title' => 'Sorozat megszakadt',
        'broken_body' => 'A :strategy sorozatod (:period) véget ért.',
    ],
];
