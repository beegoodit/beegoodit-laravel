<?php

namespace BeegoodIT\FilamentTenancyRoles\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TeamRole: string implements HasColor, HasLabel
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Owner => 'success',
            self::Admin => 'info',
            self::Member => 'gray',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::Owner => __('Owner'),
            self::Admin => __('Admin'),
            self::Member => __('Member'),
        };
    }
}
