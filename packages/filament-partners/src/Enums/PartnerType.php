<?php

namespace BeegoodIT\FilamentPartners\Enums;

enum PartnerType: string
{
    case Partner = 'partner';
    case Sponsor = 'sponsor';

    public function label(): string
    {
        return __('filament-partners::partner.type.'.$this->value);
    }
}
