<?php

namespace BeegoodIT\FilamentEntryLinks\Enums;

enum EntryLinkRedirectCode: int
{
    case Permanent = 301;
    case Temporary = 302;

    public function label(): string
    {
        return match ($this) {
            self::Permanent => __('filament-entry-links::filament.redirect_code.permanent'),
            self::Temporary => __('filament-entry-links::filament.redirect_code.temporary'),
        };
    }
}
