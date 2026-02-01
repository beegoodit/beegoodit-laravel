<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources;

use BeegoodIT\LaravelPwa\Filament\Resources\MessageResource\Pages;
use BeegoodIT\LaravelPwa\Models\Notifications\Message;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    public static function getModelLabel(): string
    {
        return __('laravel-pwa::notifications.messages.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('laravel-pwa::notifications.messages.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('laravel-pwa::notifications.messages.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.settings');
    }

    public static function getNavigationSort(): ?int
    {
        return 125;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
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
                        TextInput::make('content.title')
                            ->label(__('laravel-pwa::broadcast.fields.title.label'))
                            ->disabled(),

                        Textarea::make('content.body')
                            ->label(__('laravel-pwa::broadcast.fields.body.label'))
                            ->disabled(),
                    ])->columns(1),
            ])->columns(1);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('laravel-pwa::notifications.messages.title'))
                    ->schema([
                        TextEntry::make('broadcast_id')
                            ->label(__('laravel-pwa::broadcast.fields.broadcast_id.label')),

                        TextEntry::make('push_subscription_id')
                            ->label(__('laravel-pwa::broadcast.fields.push_subscription_id.label')),

                        TextEntry::make('delivery_status')
                            ->label(__('laravel-pwa::broadcast.fields.status.label'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'sent' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __("laravel-pwa::broadcast.fields.status.options.{$state}")),

                        TextEntry::make('opened_at')
                            ->label(__('laravel-pwa::broadcast.fields.opened_at.label'))
                            ->dateTime(),

                        TextEntry::make('error_message')
                            ->label(__('laravel-pwa::broadcast.fields.error_message.label'))
                            ->visible(fn ($record) => $record->error_message !== null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Content')
                    ->schema([
                        TextEntry::make('content.title')
                            ->label(__('laravel-pwa::broadcast.fields.title.label'))
                            ->weight('bold'),

                        TextEntry::make('content.body')
                            ->label(__('laravel-pwa::broadcast.fields.body.label')),
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
                    ->sortable(),

                TextColumn::make('broadcast.payload.title')
                    ->label(__('laravel-pwa::broadcast.fields.broadcast.label'))
                    ->searchable()
                    ->limit(30),

                TextColumn::make('pushSubscription.user.name')
                    ->label(__('laravel-pwa::broadcast.fields.user.label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pushSubscription.endpoint')
                    ->label(__('laravel-pwa::broadcast.fields.recipient.label'))
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivery_status')
                    ->label(__('laravel-pwa::broadcast.fields.status.label'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("laravel-pwa::broadcast.fields.status.options.{$state}")),

                TextColumn::make('opened_at')
                    ->label(__('laravel-pwa::broadcast.fields.opened_at.label'))
                    ->dateTime()
                    ->sortable(),
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
                    ->action(function (Message $record) {
                        $record->update([
                            'delivery_status' => 'pending',
                            'error_message' => null,
                        ]);

                        dispatch(new \BeegoodIT\LaravelPwa\Notifications\Jobs\SendMessageJob($record))
                            ->onQueue(config('pwa.notifications.queue', 'default'));

                        \Filament\Notifications\Notification::make()
                            ->title(__('laravel-pwa::broadcast.notifications.requeued.title'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Message $record) => $record->delivery_status === 'failed'),
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
