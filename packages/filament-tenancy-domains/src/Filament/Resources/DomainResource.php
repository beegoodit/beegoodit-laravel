<?php

namespace BeegoodIT\FilamentTenancyDomains\Filament\Resources;

use BackedEnum;
use BeegoodIT\FilamentTenancyDomains\Domain;
use BeegoodIT\FilamentTenancyDomains\Filament\Resources\DomainResource\Pages;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                            ->label(fn (Get $get): string => $get('type') === 'platform'
                                ? __('filament-tenancy-domains::domains.platform_subdomain')
                                : __('filament-tenancy-domains::domains.domain'))
                            ->prefix('https://')
                            ->suffix(fn (Get $get): ?string => $get('type') === 'platform' ? '.'.config('filament-tenancy-domains.platform_domain', 'domain.local') : null)
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, Get $get, ?string $state) {
                                $platformDomain = config('filament-tenancy-domains.platform_domain', 'domain.local');
                                if ($get('type') === 'platform' && $state && ! str_contains($state, '.'.$platformDomain)) {
                                    return $rule->where('domain', $state.'.'.$platformDomain);
                                }

                                return $rule;
                            })
                            ->formatStateUsing(function ($state, Get $get): mixed {
                                $platformDomain = config('filament-tenancy-domains.platform_domain', 'domain.local');
                                if ($get('type') === 'platform' && $state) {
                                    return str_replace('.'.$platformDomain, '', $state);
                                }

                                return $state;
                            })
                            ->dehydrateStateUsing(function (?string $state, Get $get): mixed {
                                $platformDomain = config('filament-tenancy-domains.platform_domain', 'domain.local');
                                if ($get('type') === 'platform' && $state && ! str_contains($state, '.'.$platformDomain)) {
                                    return $state.'.'.$platformDomain;
                                }

                                return $state;
                            })
                            ->helperText(fn (Get $get): ?string => $get('type') === 'platform'
                                ? __('filament-tenancy-domains::domains.platform_subdomain_helper')
                                : null)
                            ->rules([
                                fn (Get $get): string => $get('type') === 'platform'
                                    ? 'regex:/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/'
                                    : 'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i',
                            ])
                            ->validationMessages([
                                'regex' => fn (Get $get): string => $get('type') === 'platform'
                                    ? __('filament-tenancy-domains::domains.invalid_subdomain')
                                    : __('filament-tenancy-domains::domains.invalid_custom_domain'),
                            ]),
                        Forms\Components\Select::make('type')
                            ->label(__('filament-tenancy-domains::domains.type'))
                            ->options([
                                'platform' => __('filament-tenancy-domains::domains.platform'),
                                'custom' => __('filament-tenancy-domains::domains.custom'),
                            ])
                            ->required()
                            ->default('platform')
                            ->disabled(fn (?Domain $record): bool => $record !== null)
                            ->live()
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
                        Forms\Components\Placeholder::make('dns_instructions')
                            ->label(__('filament-tenancy-domains::domains.dns_setup'))
                            ->content(fn (?Domain $record): \Illuminate\Support\HtmlString => new HtmlString(sprintf(
                                '<div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 font-mono text-sm space-y-2">'.
                                '<div><span class="text-gray-500">%s:</span> TXT</div>'.
                                '<div><span class="text-gray-500">%s:</span> _foosbeaver-verification.%s</div>'.
                                '<div><span class="text-gray-500">%s:</span> <span class="text-primary-600 dark:text-primary-400">%s</span></div>'.
                                '</div>',
                                __('filament-tenancy-domains::domains.dns_record_type'),
                                __('filament-tenancy-domains::domains.dns_record_name'),
                                $record?->domain,
                                __('filament-tenancy-domains::domains.dns_record_value'),
                                $record?->verification_token ?? '-'
                            )))
                            ->visible(fn (?Domain $record): bool => $record && ! $record->is_verified),

                        Forms\Components\Placeholder::make('verification_status')
                            ->hiddenLabel()
                            ->content(function (?Domain $record): ?\Illuminate\Support\HtmlString {
                                if (! $record instanceof \BeegoodIT\FilamentTenancyDomains\Domain) {
                                    return null;
                                }

                                $bgClass = $record->is_verified
                                    ? 'bg-green-50 text-green-700 border-green-100 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800'
                                    : ($record->last_verification_error
                                        ? 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800'
                                        : 'bg-yellow-50 text-yellow-700 border-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400 dark:border-yellow-800');

                                $statusLabel = $record->is_verified
                                    ? __('filament-tenancy-domains::domains.verified')
                                    : ($record->last_verification_error
                                        ? __('filament-tenancy-domains::domains.failed')
                                        : __('filament-tenancy-domains::domains.pending'));

                                $errorMessage = $record->last_verification_error
                                    ? ': '.e($record->last_verification_error)
                                    : '';

                                return new HtmlString(sprintf(
                                    '<div class="rounded-lg border p-3 text-sm %s" wire:poll.5s>'.
                                    '<span class="font-bold uppercase tracking-wide text-xs">%s</span>'.
                                    '<span>%s</span>'.
                                    '</div>',
                                    $bgClass,
                                    $statusLabel,
                                    $errorMessage
                                ));
                            })
                            ->visible(fn (?Domain $record): ?\BeegoodIT\FilamentTenancyDomains\Domain => $record),

                        Actions::make([
                            Action::make('verify_now_inline')
                                ->label(__('filament-tenancy-domains::domains.verify_now'))
                                ->button()
                                ->color('primary')
                                ->action(function (Domain $record): void {
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
                                ->visible(fn (Domain $record): bool => $record->type !== 'platform' && ! $record->is_verified),
                        ])->visible(fn (?Domain $record): bool => $record && ! $record->is_verified),

                        Forms\Components\TextInput::make('verification_token')
                            ->label(__('filament-tenancy-domains::domains.verification_token'))
                            ->maxLength(255)
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => $get('type') !== 'platform' && ! $get('verification_token')),

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label(__('filament-tenancy-domains::domains.verified_at'))
                            ->visible(fn (?Domain $record) => $record?->is_verified),

                        Forms\Components\DateTimePicker::make('last_verification_attempt_at')
                            ->label(__('filament-tenancy-domains::domains.last_verification_attempt'))
                            ->disabled()
                            ->visible(fn (?Domain $record): bool => $record && ! $record->is_verified),

                        Forms\Components\Textarea::make('last_verification_error')
                            ->label(__('filament-tenancy-domains::domains.last_verification_error'))
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => $get('last_verification_error')),
                    ])
                    ->columns(1)
                    ->hidden(fn (Get $get): bool => $get('type') === 'platform'),
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
                        'success' => 'custom',
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
                    ->boolean()
                    ->getStateUsing(fn ($record): ?bool => $record->type === 'platform' ? null : $record->is_verified),
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
                        'custom' => __('filament-tenancy-domains::domains.custom'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label(__('filament-tenancy-domains::domains.is_verified')),
            ])
            ->recordActions([
                Action::make('verify')
                    ->label(__('filament-tenancy-domains::domains.verify_now'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(function (Domain $record): void {
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
                    ->visible(fn (Domain $record): bool => $record->type !== 'platform' && ! $record->is_verified),
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
