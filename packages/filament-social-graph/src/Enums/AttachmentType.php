<?php

namespace BeegoodIT\FilamentSocialGraph\Enums;

enum AttachmentType: string
{
    case File = 'file';
    case Image = 'image';

    public function label(): string
    {
        return match ($this) {
            self::File => __('filament-social-graph::attachment.file'),
            self::Image => __('filament-social-graph::attachment.image'),
        };
    }
}
