<?php

return [
    'name' => 'Nyitvatartási ütemterv',
    'plural' => 'Nyitvatartási ütemtervek',
    'navigation_group' => 'Nyitvatartás',
    'relation_title' => 'Nyitvatartás',
    'openable_label' => 'Helyszín',
    'timezone_label' => 'Időzóna',
    'active_from_label' => 'Érvényes ettől',
    'active_to_label' => 'Érvényes eddig',
    'closed_label' => 'Zárva',
    'intervals_label' => 'Idősávok',
    'opens_at_label' => 'Nyitás',
    'closes_at_label' => 'Zárás',
    'add_interval' => 'Idősáv hozzáadása',
    'overnight_hint' => 'A zárási idő a nyitásnál korábban van ugyanazon a napon — ez az idősáv éjfél után is tart.',
    'validation' => [
        'overlapping_active_window' => 'Ez az időszak átfed egy másik ütemtervet ugyanazon a helyen.',
    ],
];
