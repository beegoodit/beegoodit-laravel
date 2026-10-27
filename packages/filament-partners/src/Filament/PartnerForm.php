<?php

namespace BeegoodIT\FilamentPartners\Filament;

use BeegoodIT\FilamentPartners\Enums\PartnerType;
use BeegoodIT\FilamentPartners\Models\Partner;
use BeegoodIT\FilamentPartners\Support\PartnerableTypes;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Date;

class PartnerForm
{
    public static function configure(Schema $schema, bool $forRelationManager = false): Schema
    {
        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
        $directory = config('filament-partners.logo_directory', 'partners');
        $maxSize = config('filament-partners.logo_max_size', 2048);
        $partnerableModels = PartnerableTypes::models();

        return $schema
            ->columns(2)
            ->components([
                MorphToSelect::make('partnerable')
                    ->label(__('filament-partners::partner.partnerable_label'))
                    ->types(PartnerableTypes::morphTypes())
                    ->columnSpanFull()
                    ->hidden(fn (): bool => $forRelationManager || Filament::hasTenancy() || $partnerableModels === [])
                    ->required(false),
                Select::make('type')
                    ->label(__('filament-partners::partner.type_label'))
                    ->options(collect(PartnerType::cases())->mapWithKeys(fn (PartnerType $t): array => [$t->value => $t->label()]))
                    ->default(PartnerType::Partner)
                    ->required()
                    ->columnSpan(1),
                TextInput::make('name')
                    ->label(__('filament-partners::partner.name_label'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),
                FileUpload::make('logo')
                    ->label(__('filament-partners::partner.logo_label'))
                    ->image()
                    ->disk($disk)
                    ->directory(fn (?Partner $record): string => $record instanceof Partner
                        ? $directory.'/'.$record->id
                        : $directory
                    )
                    ->maxSize($maxSize)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
                    ->visibility('private')
                    ->deletable()
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label(__('filament-partners::partner.description_label'))
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->label(__('filament-partners::partner.url_label'))
                    ->url()
                    ->maxLength(1024)
                    ->columnSpanFull(),
                DateTimePicker::make('active_from')
                    ->label(__('filament-partners::partner.active_from_label'))
                    ->required()
                    ->default(Date::parse('1970-01-01 00:00:00'))
                    ->columnSpan(1),
                DateTimePicker::make('active_to')
                    ->label(__('filament-partners::partner.active_to_label'))
                    ->required()
                    ->default(Date::parse('9999-12-31 23:59:59'))
                    ->columnSpan(1),
            ]);
    }
}
