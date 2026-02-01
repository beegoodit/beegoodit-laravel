<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource\RelationManagers;

use BeegoodIT\LaravelPwa\Notifications\Jobs\SendMessageJob;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('laravel-pwa::notifications.messages.title');
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('pushSubscription.user.name')
                            ->label(__('laravel-pwa::broadcast.fields.user.label')),

                        \Filament\Infolists\Components\TextEntry::make('delivery_status')
                            ->label(__('laravel-pwa::broadcast.fields.status.label'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'sent' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __("laravel-pwa::broadcast.fields.status.options.{$state}")),

                        \Filament\Infolists\Components\TextEntry::make('opened_at')
                            ->label(__('laravel-pwa::broadcast.fields.opened_at.label'))
                            ->dateTime(),

                        \Filament\Infolists\Components\TextEntry::make('error_message')
                            ->label(__('laravel-pwa::broadcast.fields.error_message.label'))
                            ->visible(fn ($record) => $record->error_message !== null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                \Filament\Schemas\Components\Section::make('Content')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('content.title')
                            ->label(__('laravel-pwa::broadcast.fields.title.label'))
                            ->weight('bold'),

                        \Filament\Infolists\Components\TextEntry::make('content.body')
                            ->label(__('laravel-pwa::broadcast.fields.body.label')),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('laravel-pwa::broadcast.fields.created_at.label'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pushSubscription.user.name')
                    ->label(__('laravel-pwa::broadcast.fields.user.label'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pushSubscription.endpoint')
                    ->label(__('laravel-pwa::broadcast.fields.recipient.label'))
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('delivery_status')
                    ->label(__('laravel-pwa::broadcast.fields.status.label'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("laravel-pwa::broadcast.fields.status.options.{$state}")),

                Tables\Columns\TextColumn::make('opened_at')
                    ->label(__('laravel-pwa::broadcast.fields.opened_at.label'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Action::make('resend')
                    ->label(__('laravel-pwa::broadcast.buttons.resend'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'delivery_status' => 'pending',
                            'error_message' => null,
                        ]);

                        dispatch(new SendMessageJob($record))
                            ->onQueue(config('pwa.notifications.queue', 'default'));

                        Notification::make()
                            ->title(__('laravel-pwa::broadcast.notifications.requeued.title'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->delivery_status === 'failed'),
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
