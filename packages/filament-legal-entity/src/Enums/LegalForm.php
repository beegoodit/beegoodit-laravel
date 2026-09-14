<?php

namespace BeegoodIT\FilamentLegalEntity\Enums;

enum LegalForm: string
{
    case Einzelunternehmen = 'einzelunternehmen';
    case Gbr = 'gbr';
    case Nev = 'nev';
    case Ev = 'ev';
    case Gmbh = 'gmbh';
    case Ug = 'ug';
    case Ag = 'ag';
    case GmbhCoKg = 'gmbh_co_kg';
    case UgCoKg = 'ug_co_kg';
    case Kg = 'kg';
    case Ohg = 'ohg';
    case Eg = 'eg';
    case Stiftung = 'stiftung';
    case Other = 'other';

    public function label(): string
    {
        return __('filament-legal-entity::entity.legal_form.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $form) {
            $options[$form->value] = $form->label();
        }

        return $options;
    }

    public function requiresRegister(): bool
    {
        return match ($this) {
            self::Ev,
            self::Gmbh,
            self::Ug,
            self::Ag,
            self::GmbhCoKg,
            self::UgCoKg,
            self::Kg,
            self::Ohg,
            self::Eg => true,
            default => false,
        };
    }

    public function showsRegisterFields(): bool
    {
        return $this->requiresRegister() || $this === self::Stiftung;
    }
}
