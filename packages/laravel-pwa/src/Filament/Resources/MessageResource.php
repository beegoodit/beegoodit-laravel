<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources;

use BeegoodIT\LaravelPwa\Filament\Resources\MessageResource\Pages;
use BeegoodIT\LaravelPwa\Models\Notifications\Message;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-paper-airplane';

    public static function getModelLabel(): string
    {
        return __('laravel-pwa::notifications.messages.resource_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('laravel-pwa::notifications.messages.resource_label_plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('laravel-pwa::notifications.messages.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('laravel-pwa::notifications.nav.group');
    }

    public static function getNavigationSort(): ?int
    {
        return 30;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('broadcast_id')
                            ->label(__('laravel-pwa::broadcast.fields.broadcast_id.label'))
                            ->disabled(),

                        TextInput::make('push_subscription_id')
                            ->label(__('laravel-pwa::broadcast.fields.push_subscription_id.label'))
                            ->disabled(),

                        TextInput::make('delivery_status')
                            ->label(__('laravel-pwa::broadcast.fields.status.label'))
                            ->disabled(),

                        DateTimePicker::make('opened_at')
                            ->label(__('laravel-pwa::broadcast.fields.opened_at.label'))
                            ->disabled(),

                        Textarea::make('error_message')
                            ->label(__('laravel-pwa::broadcast.fields.error_message.label'))
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Content')
                    ->schema([
                        TextInput::make('title')
                            ->label(__('laravel-pwa::broadcast.fields.title.label'))
                            ->state(fn ($record) => $record->resolveContent()->title ?? '-')
                            ->disabled(),

                        Textarea::make('body')
                            ->label(__('laravel-pwa::broadcast.fields.body.label'))
                            ->state(fn ($record) => $record->resolveContent()->body ?? '-')
                            ->disabled(),
                    ])->columns(1),
            ])->columns(1);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('laravel-pwa::notifications.messages.resource_label'))
                    ->schema([
                        TextEntry::make('delivery_status')
                            ->label(__('laravel-pwa::broadcast.fields.status.label'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'on_hold' => 'warning',
                                'sent' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __("laravel-pwa::broadcast.fields.status.options.{$state}")),

                        TextEntry::make('pushSubscription.user.name')
                            ->label(__('laravel-pwa::broadcast.fields.user.label')),

                        TextEntry::make('opened_at')
                            ->label(__('laravel-pwa::broadcast.fields.opened_at.label'))
                            ->dateTime(),

                        TextEntry::make('error_message')
                            ->label(__('laravel-pwa::broadcast.fields.error_message.label'))
                            ->visible(fn ($record): bool => $record->error_message !== null)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make(__('laravel-pwa::notifications.broadcasts.content'))
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('laravel-pwa::broadcast.fields.title.label'))
                            ->state(fn ($record) => $record->resolveContent()->title ?? '-')
                            ->weight('bold'),

                        TextEntry::make('body')
                            ->label(__('laravel-pwa::broadcast.fields.body.label'))
                            ->state(fn ($record) => $record->resolveContent()->body ?? '-'),

                        TextEntry::make('action_url')
                            ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                            ->state(fn ($record) => $record->resolveContent()->data['url'] ?? null)
                            ->url(fn ($record) => $record->resolveContent()->data['url'] ?? null, true)
                            ->placeholder('-'),
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

                TextColumn::make('title')
                    ->label(__('laravel-pwa::broadcast.fields.title.label'))
                    ->state(fn ($record) => $record->resolveContent()->title ?? '-')
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->resolveContent()->body ?? null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pushSubscription.user.name')
                    ->label(__('laravel-pwa::broadcast.fields.user.label'))
                    ->size('xs')
                    ->color('gray'),

                TextColumn::make('action_url')
                    ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                    ->state(fn ($record) => $record->resolveContent()->data['url'] ?? null)
                    ->icon('heroicon-o-link')
                    ->url(fn ($record) => $record->resolveContent()->data['url'] ?? null, true)
                    ->color('primary')
                    ->size('xs')
                    ->limit(20)
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('broadcast.payload.title')
                    ->label(__('laravel-pwa::broadcast.fields.broadcast.label'))
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivery_status')
                    ->label(__('laravel-pwa::broadcast.fields.status.label'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'on_hold' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("laravel-pwa::broadcast.fields.status.options.{$state}")),

                TextColumn::make('opened_at')
                    ->label(__('laravel-pwa::broadcast.fields.opened_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('hold')
                    ->label(__('laravel-pwa::notifications.messages.actions.hold'))
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->visible(fn (Message $record): bool => $record->delivery_status === 'pending')
                    ->action(fn (Message $record) => $record->hold()),

                Action::make('release')
                    ->label(__('laravel-pwa::notifications.messages.actions.release'))
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->visible(fn (Message $record): bool => $record->delivery_status === 'on_hold')
                    ->action(fn (Message $record) => $record->release()),

                Action::make('resend')
                    ->label(__('laravel-pwa::broadcast.buttons.resend'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Message $record): bool => in_array($record->delivery_status, ['sent', 'failed']))
                    ->action(function (Message $record): void {
                        $record->release(); // Set to pending

                        dispatch(new \BeegoodIT\LaravelPwa\Notifications\Jobs\SendMessageJob($record))
                            ->onQueue(config('pwa.notifications.queue', 'default'));

                        \Filament\Notifications\Notification::make()
                            ->title(__('laravel-pwa::broadcast.notifications.requeued.title'))
                            ->success()
                            ->send();
                    }),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
            'view' => Pages\ViewMessage::route('/{record}'),
        ];
    }
}
