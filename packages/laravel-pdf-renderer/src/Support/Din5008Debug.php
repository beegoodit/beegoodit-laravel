<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Support;

/** Resolves whether DIN layout debug outlines are enabled. */
final class Din5008Debug
{
    public static function enabled(?bool $override = null): bool
    {
        if ($override !== null) {
            return $override;
        }

        return (bool) config('laravel-pdf-renderer.debug_layout', false);
    }
}
