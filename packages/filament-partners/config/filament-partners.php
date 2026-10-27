<?php

return [
    'logo_directory' => env('FILAMENT_PARTNERS_LOGO_DIRECTORY', 'partners'),
    'logo_max_size' => env('FILAMENT_PARTNERS_LOGO_MAX_SIZE', 2048),

    /*
     * Models that can own partners (e.g. Team). Used for Admin MorphToSelect when not in tenant context.
     * List of class names, or a map of class => title attribute (e.g. Event::class => 'title').
     * Example: [\App\Models\Team::class]
     */
    'partnerable_models' => [],

    /*
     * Optional per-model MorphToSelect title attribute. Defaults to "name".
     * Use a method name (e.g. title) when the model exposes title() instead of a name column.
     * Example: [\App\Models\Event::class => 'title']
     */
    'partnerable_title_attributes' => [],
];
