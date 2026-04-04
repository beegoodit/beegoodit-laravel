<?php

namespace BeegoodIT\FilamentEntryLinks\Support;

final class PublicEntryViews
{
    public static function unavailable(): string
    {
        return self::usesPublicLayout()
            ? 'filament-entry-links::unavailable-layout'
            : 'filament-entry-links::unavailable';
    }

    public static function comingSoon(): string
    {
        return self::usesPublicLayout()
            ? 'filament-entry-links::coming-soon-layout'
            : 'filament-entry-links::coming-soon';
    }

    public static function usesPublicLayout(): bool
    {
        $layout = config('filament-entry-links.public_layout');

        return is_string($layout) && $layout !== '';
    }
}
