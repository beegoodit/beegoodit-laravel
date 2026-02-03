<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources;

use BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource\Pages;
use BeegoodIT\LaravelPwa\Models\Notifications\Broadcast;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BroadcastResource extends Resource
{
    protected static ?string $model = Broadcast::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-megaphone';
    }

    public static function getModelLabel(): string
    {
        return __('laravel-pwa::notifications.broadcasts.resource_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('laravel-pwa::notifications.broadcasts.resource_label_plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('laravel-pwa::notifications.broadcasts.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('laravel-pwa::notifications.nav.group');
    }

    public static function getNavigationSort(): ?int
    {
        return 20;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getLabel(): string
    {
        return __('laravel-pwa::notifications.broadcasts.resource_label');
    }

    public static function getPluralLabel(): string
    {
        return __('laravel-pwa::notifications.broadcasts.resource_label_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('trigger_type')
                            ->label(__('laravel-pwa::broadcast.fields.target_type.label'))
                            ->disabled(),

                        TextInput::make('status')
                            ->label(__('laravel-pwa::broadcast.fields.status.label'))
                            ->disabled(),

                        TextInput::make('total_recipients')
                            ->label(__('laravel-pwa::broadcast.fields.total_recipients.label'))
                            ->numeric()
                            ->disabled(),

                        TextInput::make('total_sent')
                            ->label(__('laravel-pwa::broadcast.fields.total_sent.label'))
                            ->numeric()
                            ->disabled(),

                        TextInput::make('total_opened')
                            ->label(__('laravel-pwa::broadcast.fields.total_opened.label'))
                            ->numeric()
                            ->disabled(),
                    ])->columns(1),

                Section::make('Content')
                    ->schema([
                        TextInput::make('display_title')
                            ->label(__('laravel-pwa::broadcast.fields.title.label'))
                            ->state(fn ($record) => $record->payload['title'] ?? Str::afterLast($record->trigger_type, '\\'))
                            ->disabled(),

                        Textarea::make('display_body')
                            ->label(__('laravel-pwa::broadcast.fields.body.label'))
                            ->state(fn ($record) => $record->payload['body'] ?? __('laravel-pwa::broadcast.fields.status.options.automated'))
                            ->disabled(),

                        TextInput::make('display_url')
                            ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                            ->state(fn ($record) => $record->payload['data']['url'] ?? '-')
                            ->disabled(),
                    ])->columns(1),
            ])->columns(1);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('laravel-pwa::notifications.broadcasts.stats'))
                    ->schema([
                        TextEntry::make('status')
                            ->label(__('laravel-pwa::broadcast.fields.status.label'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'processing' => 'warning',
                                'completed' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __("laravel-pwa::broadcast.fields.status.options.{$state}")),

                        TextEntry::make('total_recipients')
                            ->label(__('laravel-pwa::broadcast.fields.total_recipients.label')),

                        TextEntry::make('total_sent')
                            ->label(__('laravel-pwa::broadcast.fields.total_sent.label')),

                        TextEntry::make('total_opened')
                            ->label(__('laravel-pwa::broadcast.fields.total_opened.label')),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make(__('laravel-pwa::notifications.broadcasts.content'))
                    ->schema([
                        TextEntry::make('display_title')
                            ->label(__('laravel-pwa::broadcast.fields.title.label'))
                            ->state(fn ($record) => $record->payload['title'] ?? Str::afterLast($record->trigger_type, '\\'))
                            ->weight('bold'),

                        TextEntry::make('trigger_type_display')
                            ->label(__('laravel-pwa::broadcast.fields.target_type.label'))
                            ->state(fn ($record) => $record->status === 'automated' ? __('laravel-pwa::broadcast.fields.status.options.automated') : ($record->target_ids ? __('laravel-pwa::broadcast.fields.target_type.options.users') : __('laravel-pwa::broadcast.fields.target_type.options.all'))),

                        TextEntry::make('display_body')
                            ->label(__('laravel-pwa::broadcast.fields.body.label'))
                            ->state(fn ($record) => $record->payload['body'] ?? '-'),

                        TextEntry::make('display_url')
                            ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                            ->state(fn ($record) => $record->payload['data']['url'] ?? '-')
                            ->url(fn ($record) => $record->payload['data']['url'] ?? null, true),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('laravel-pwa::broadcast.fields.created_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('display_title')
                    ->label(__('laravel-pwa::broadcast.fields.title.label'))
                    ->state(fn ($record) => $record->payload['title'] ?? Str::afterLast($record->trigger_type, '\\'))
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->payload['body'] ?? ($record->status === 'automated' ? $record::class : null))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('trigger_type_display')
                    ->label(__('laravel-pwa::broadcast.fields.target_type.label'))
                    ->state(fn ($record) => $record->target_ids ? __('laravel-pwa::broadcast.fields.target_type.options.users') : __('laravel-pwa::broadcast.fields.target_type.options.all'))
                    ->size('xs')
                    ->color('gray'),

                TextColumn::make('display_url')
                    ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                    ->state(fn ($record) => $record->payload['data']['url'] ?? '-')
                    ->icon('heroicon-o-link')
                    ->url(fn ($record) => $record->payload['data']['url'] ?? null, true)
                    ->color('primary')
                    ->size('xs')
                    ->limit(20)
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('laravel-pwa::broadcast.fields.status.label'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("laravel-pwa::broadcast.fields.status.options.{$state}")),

                TextColumn::make('total_recipients')
                    ->label(__('laravel-pwa::broadcast.fields.total_recipients.label'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_sent')
                    ->label(__('laravel-pwa::broadcast.fields.total_sent.label'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_opened')
                    ->label(__('laravel-pwa::broadcast.fields.total_opened.label'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('resend')
                    ->label(__('laravel-pwa::broadcast.buttons.resend'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Broadcast $record): void {
                        $record->update(['status' => 'pending']);

                        dispatch(new \BeegoodIT\LaravelPwa\Notifications\Jobs\ProcessBroadcastJob($record))
                            ->onQueue(config('pwa.notifications.queue', 'default'));

                        \Filament\Notifications\Notification::make()
                            ->title(__('laravel-pwa::broadcast.notifications.requeued.title'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Broadcast $record): bool => in_array($record->status, ['completed', 'failed'])),
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            BroadcastResource\RelationManagers\MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBroadcasts::route('/'),
            'create' => Pages\CreateBroadcast::route('/create'),
            'view' => Pages\ViewBroadcast::route('/{record}'),
        ];
    }
}
