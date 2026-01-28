<?php

namespace BeegoodIT\FilamentTenancyDomains\Filament\Resources;

use BackedEnum;
use BeegoodIT\FilamentTenancyDomains\Domain;
use BeegoodIT\FilamentTenancyDomains\Filament\Resources\DomainResource\Pages;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getLabel(): string
    {
        return __('filament-tenancy-domains::domains.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament-tenancy-domains::domains.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('domain')
                            ->label(__('filament-tenancy-domains::domains.domain'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label(__('filament-tenancy-domains::domains.type'))
                            ->options([
                                'platform' => __('filament-tenancy-domains::domains.platform'),
                                'custom_subdomain' => __('filament-tenancy-domains::domains.custom_subdomain'),
                                'custom_domain' => __('filament-tenancy-domains::domains.custom_domain'),
                            ])
                            ->required()
                            ->reactive(),
                    ]),
                Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_primary')
                            ->label(__('filament-tenancy-domains::domains.is_primary'))
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('filament-tenancy-domains::domains.is_active'))
                            ->default(true),
                    ]),
                Forms\Components\MorphToSelect::make('model')
                    ->label(__('filament-tenancy-domains::domains.model'))
                    ->types(collect(\Filament\Facades\Filament::getPlugin('filament-tenancy-domains')->getDomainableModels())
                        ->map(fn ($model) => Forms\Components\MorphToSelect\Type::make($model)->titleAttribute('name'))
                        ->toArray())
                    ->required(),

                Section::make(__('filament-tenancy-domains::domains.verification'))
                    ->schema([
                        Forms\Components\TextInput::make('verification_token')
                            ->label(__('filament-tenancy-domains::domains.verification_token'))
                            ->maxLength(255)
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label(__('filament-tenancy-domains::domains.verified_at')),
                        Forms\Components\DateTimePicker::make('last_verification_attempt_at')
                            ->label(__('filament-tenancy-domains::domains.last_verification_attempt'))
                            ->disabled(),
                        Forms\Components\Textarea::make('last_verification_error')
                            ->label(__('filament-tenancy-domains::domains.last_verification_error'))
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->hidden(fn (Get $get) => $get('type') === 'platform'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->label(__('filament-tenancy-domains::domains.domain'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('filament-tenancy-domains::domains.type'))
                    ->badge()
                    ->colors([
                        'primary' => 'platform',
                        'warning' => 'custom_subdomain',
                        'success' => 'custom_domain',
                    ])
                    ->formatStateUsing(fn ($state) => __('filament-tenancy-domains::domains.'.$state)),
                Tables\Columns\TextColumn::make('model_type')
                    ->label(__('filament-tenancy-domains::domains.entity_type'))
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label(__('filament-tenancy-domains::domains.is_primary'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament-tenancy-domains::domains.is_active'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label(__('filament-tenancy-domains::domains.is_verified'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('filament-tenancy-domains::domains.type'))
                    ->options([
                        'platform' => __('filament-tenancy-domains::domains.platform'),
                        'custom_subdomain' => __('filament-tenancy-domains::domains.custom_subdomain'),
                        'custom_domain' => __('filament-tenancy-domains::domains.custom_domain'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label(__('filament-tenancy-domains::domains.is_verified')),
            ])
            ->recordActions([
                Action::make('verify')
                    ->label(__('filament-tenancy-domains::domains.verify_now'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(function (Domain $record) {
                        if ($record->verify()) {
                            Notification::make()
                                ->title(__('filament-tenancy-domains::domains.verification_success'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('filament-tenancy-domains::domains.verification_failed'))
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    })
                    ->visible(fn (Domain $record) => $record->type !== 'platform' && !$record->is_verified),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDomains::route('/'),
            'create' => Pages\CreateDomain::route('/create'),
            'edit' => Pages\EditDomain::route('/{record}/edit'),
        ];
    }
}
