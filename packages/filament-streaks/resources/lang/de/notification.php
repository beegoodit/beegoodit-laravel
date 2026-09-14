<?php

return [
    'label' => 'Serien-Benachrichtigung',
    'plural' => 'Serien-Benachrichtigungen',
    'navigation_group' => 'Serien',
    'fields' => [
        'streak' => 'Serie',
        'kind' => 'Art',
        'period_key' => 'Periode',
        'sent_at' => 'Gesendet am',
    ],
    'kinds' => [
        'at_risk' => 'Gefährdet',
        'broken' => 'Unterbrochen',
    ],
    'mail' => [
        'at_risk_subject' => 'Deine Serie ist gefährdet',
        'at_risk_body' => 'Deine :strategy-Serie für die Periode :period hat noch kein qualifizierendes Ereignis.',
        'broken_subject' => 'Deine Serie wurde unterbrochen',
        'broken_body' => 'Deine :strategy-Serie für die Periode :period endete ohne qualifizierendes Ereignis.',
    ],
    'push' => [
        'at_risk_title' => 'Serie gefährdet',
        'at_risk_body' => 'Deine :strategy-Serie für :period braucht diese Woche noch ein Spiel.',
        'broken_title' => 'Serie unterbrochen',
        'broken_body' => 'Deine :strategy-Serie für :period ist beendet.',
    ],
];
