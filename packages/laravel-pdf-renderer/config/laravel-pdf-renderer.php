<?php

declare(strict_types=1);

return [
    'fake' => (bool) env('BEEGOODIT_PDF_RENDER_FAKE', false),
    'executable' => env(
        'BEEGOODIT_PDF_RENDER_EXECUTABLE',
        env('CHROMIUM_PATH', env('GROVER_EXECUTABLE_PATH')),
    ),
    'no_sandbox' => (bool) env('BEEGOODIT_PDF_RENDER_NO_SANDBOX', env('GROVER_NO_SANDBOX', true)),
    'timeout' => (int) env('BEEGOODIT_PDF_RENDER_TIMEOUT', 60),
    /** Outline DIN zones / Folgeseite chrome with colored borders (dev only). */
    'debug_layout' => (bool) env('BEEGOODIT_PDF_RENDER_DEBUG_LAYOUT', false),
];
