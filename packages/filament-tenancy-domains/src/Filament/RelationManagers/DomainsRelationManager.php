<?php

namespace BeegoodIT\FilamentTenancyDomains\Filament\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class DomainsRelationManager extends RelationManager
{
    protected static string $relationship = 'domains';

    protected static ?string $recordTitleAttribute = 'domain';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
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
                            : 'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i',
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
                    ->disabled(fn ($record): bool => $record !== null)
                    ->live()
                    ->reactive(),
                Forms\Components\Toggle::make('is_primary')
                    ->label(__('filament-tenancy-domains::domains.is_primary'))
                    ->default(false),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('filament-tenancy-domains::domains.is_active'))
                    ->default(true),
                Section::make(__('filament-tenancy-domains::domains.verification'))
                    ->schema([
                        Forms\Components\Placeholder::make('dns_instructions')
                            ->label(__('filament-tenancy-domains::domains.dns_setup'))
                            ->content(fn ($record): \Illuminate\Support\HtmlString => new HtmlString(sprintf(
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
                            ->visible(fn ($record): bool => $record && ! $record->is_verified),

                        Forms\Components\Placeholder::make('verification_status')
                            ->hiddenLabel()
                            ->content(function ($record): ?\Illuminate\Support\HtmlString {
                                if (! $record) {
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
                            ->visible(fn ($record) => $record),

                        Actions::make([
                            Action::make('verify_now_modal')
                                ->label(__('filament-tenancy-domains::domains.verify_now'))
                                ->button()
                                ->color('primary')
                                ->action(function ($record): void {
                                    if ($record->verify()) {
                                        \Filament\Notifications\Notification::make()
                                            ->title(__('filament-tenancy-domains::domains.verification_success'))
                                            ->success()
                                            ->send();
                                    } else {
                                        \Filament\Notifications\Notification::make()
                                            ->title(__('filament-tenancy-domains::domains.verification_failed'))
                                            ->danger()
                                            ->persistent()
                                            ->send();
                                    }
                                })
                                ->visible(fn ($record): bool => $record && ! $record->is_verified),
                        ])->visible(fn ($record): bool => $record && ! $record->is_verified),

                        Forms\Components\TextInput::make('verification_token')
                            ->label(__('filament-tenancy-domains::domains.verification_token'))
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (Get $get): bool => $get('type') !== 'platform' && ! $get('verification_token')), // Fallback if record is new

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label(__('filament-tenancy-domains::domains.verified_at'))
                            ->disabled()
                            ->visible(fn ($record) => $record?->is_verified),
                    ])
                    ->hidden(fn (Get $get): bool => $get('type') === 'platform'),
            ]);
    }

    public function table(Table $table): Table
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
                Tables\Columns\IconColumn::make('is_primary')
                    ->label(__('filament-tenancy-domains::domains.is_primary'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament-tenancy-domains::domains.is_active'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label(__('filament-tenancy-domains::domains.is_verified'))
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('verify')
                    ->label(__('filament-tenancy-domains::domains.verify_now'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(function (\BeegoodIT\FilamentTenancyDomains\Domain $record): void {
                        if ($record->verify()) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('filament-tenancy-domains::domains.verification_success'))
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title(__('filament-tenancy-domains::domains.verification_failed'))
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    })
                    ->visible(fn (\BeegoodIT\FilamentTenancyDomains\Domain $record): bool => $record->type !== 'platform' && ! $record->is_verified),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
