<?php

return [
    'logo_disk' => env('FILAMENT_PARTNERS_LOGO_DISK'),
    'logo_directory' => env('FILAMENT_PARTNERS_LOGO_DIRECTORY', 'partners'),
    'logo_max_size' => env('FILAMENT_PARTNERS_LOGO_MAX_SIZE', 2048),

    /*
     * Models that can own partners (e.g. Team). Used for Admin MorphToSelect when not in tenant context.
     * Example: [\App\Models\Team::class]
     */
    'partnerable_models' => [],
];
