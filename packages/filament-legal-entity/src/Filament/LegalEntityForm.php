<?php

namespace BeegoodIT\FilamentLegalEntity\Filament;

use BeegoodIT\FilamentLegalEntity\Enums\LegalForm;
use BeegoodIT\FilamentLegalEntity\FilamentLegalEntityPlugin;
use BeegoodIT\FilamentLegalEntity\Profiles\CountryProfileRegistry;
use BeegoodIT\FilamentLegalEntity\Support\Iso3166Countries;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

final class LegalEntityForm
{
    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    public static function schema(bool $includeOwner = true, bool $coreRequired = true): array
    {
        $components = [];

        if ($includeOwner) {
            $components[] = self::ownerSelect();
        }

        $components[] = Select::make('country')
            ->label(__('filament-legal-entity::entity.country'))
            ->options(fn (): array => Iso3166Countries::options())
            ->searchable()
            ->live()
            ->required($coreRequired)
            ->columnSpanFull();

        $components[] = TextInput::make('name')
            ->label(__('filament-legal-entity::entity.name'))
            ->required($coreRequired)
            ->maxLength(255)
            ->columnSpanFull();

        $components[] = TextInput::make('address_line')
            ->label(__('filament-legal-entity::entity.address_line'))
            ->required($coreRequired)
            ->maxLength(255)
            ->columnSpanFull();

        $components[] = TextInput::make('address_line_2')
            ->label(__('filament-legal-entity::entity.address_line_2'))
            ->maxLength(255)
            ->columnSpanFull();

        $components[] = TextInput::make('postal_code')
            ->label(__('filament-legal-entity::entity.postal_code'))
            ->required($coreRequired)
            ->maxLength(32);

        $components[] = TextInput::make('city')
            ->label(__('filament-legal-entity::entity.city'))
            ->required($coreRequired)
            ->maxLength(255);

        $components[] = TextInput::make('region')
            ->label(__('filament-legal-entity::entity.region'))
            ->maxLength(255)
            ->hidden(fn (Get $get): bool => CountryProfileRegistry::isDe($get('country')))
            ->columnSpanFull();

        $components[] = TextInput::make('email')
            ->label(__('filament-legal-entity::entity.email'))
            ->email()
            ->required($coreRequired)
            ->maxLength(255);

        $components[] = TextInput::make('phone')
            ->label(__('filament-legal-entity::entity.phone'))
            ->tel()
            ->maxLength(64);

        $components[] = Section::make(__('filament-legal-entity::entity.de_section'))
            ->visible(fn (Get $get): bool => CountryProfileRegistry::isDe($get('country')))
            ->schema(self::deExtras())
            ->columnSpanFull();

        return $components;
    }

    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    private static function deExtras(): array
    {
        return [
            Select::make('profile.legal_form')
                ->label(__('filament-legal-entity::entity.legal_form_label'))
                ->options(fn (): array => LegalForm::options())
                ->required(fn (Get $get): bool => CountryProfileRegistry::isDe($get('country')))
                ->live()
                ->columnSpanFull(),
            TextInput::make('profile.legal_form_other')
                ->label(__('filament-legal-entity::entity.legal_form_other'))
                ->maxLength(255)
                ->visible(fn (Get $get): bool => $get('profile.legal_form') === LegalForm::Other->value)
                ->required(fn (Get $get): bool => $get('profile.legal_form') === LegalForm::Other->value)
                ->columnSpanFull(),
            TextInput::make('profile.representative')
                ->label(__('filament-legal-entity::entity.representative'))
                ->required(fn (Get $get): bool => CountryProfileRegistry::isDe($get('country')))
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('profile.vat_id')
                ->label(__('filament-legal-entity::entity.vat_id'))
                ->maxLength(32),
            TextInput::make('profile.register_court')
                ->label(__('filament-legal-entity::entity.register_court'))
                ->maxLength(255)
                ->visible(fn (Get $get): bool => self::showsRegister($get('profile.legal_form')))
                ->required(fn (Get $get): bool => self::requiresRegister($get('profile.legal_form'))),
            TextInput::make('profile.register_number')
                ->label(__('filament-legal-entity::entity.register_number'))
                ->maxLength(255)
                ->visible(fn (Get $get): bool => self::showsRegister($get('profile.legal_form')))
                ->required(fn (Get $get): bool => self::requiresRegister($get('profile.legal_form'))),
            DatePicker::make('profile.founded_at')
                ->label(__('filament-legal-entity::entity.founded_at')),
        ];
    }

    private static function ownerSelect(): MorphToSelect
    {
        $models = [];

        try {
            $models = FilamentLegalEntityPlugin::get()->getLegalableModels();
        } catch (\Throwable) {
            $models = [];
        }

        return MorphToSelect::make('owner')
            ->label(__('filament-legal-entity::entity.owner'))
            ->types(collect($models)
                ->map(fn (string $model): MorphToSelect\Type => MorphToSelect\Type::make($model)->titleAttribute('name'))
                ->all())
            ->columnSpanFull()
            ->hidden(fn (): bool => self::filamentHasTenancy() || $models === [])
            ->required(false);
    }

    private static function filamentHasTenancy(): bool
    {
        try {
            return filament()->hasTenancy();
        } catch (\Throwable) {
            return false;
        }
    }

    private static function showsRegister(mixed $legalForm): bool
    {
        return LegalForm::tryFrom((string) $legalForm)?->showsRegisterFields() ?? false;
    }

    private static function requiresRegister(mixed $legalForm): bool
    {
        return LegalForm::tryFrom((string) $legalForm)?->requiresRegister() ?? false;
    }
}
