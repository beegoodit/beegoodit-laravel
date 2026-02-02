<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource\RelationManagers;

use BeegoodIT\LaravelPwa\Notifications\Jobs\SendMessageJob;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

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
                            ->visible(fn ($record): bool => $record->error_message !== null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                \Filament\Schemas\Components\Section::make('Content')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('title')
                            ->label(__('laravel-pwa::broadcast.fields.title.label'))
                            ->state(fn ($record) => $record->resolveContent()->title ?? '-')
                            ->weight('bold'),

                        \Filament\Infolists\Components\TextEntry::make('body')
                            ->label(__('laravel-pwa::broadcast.fields.body.label'))
                            ->state(fn ($record) => $record->resolveContent()->body ?? '-'),

                        \Filament\Infolists\Components\TextEntry::make('action_url')
                            ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                            ->state(fn ($record) => $record->resolveContent()->data['url'] ?? null)
                            ->url(fn ($record) => $record->resolveContent()->data['url'] ?? null, true)
                            ->placeholder('-'),
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

                Tables\Columns\TextColumn::make('title')
                    ->label(__('laravel-pwa::broadcast.fields.title.label'))
                    ->state(fn ($record) => $record->resolveContent()->title ?? '-')
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->resolveContent()->body ?? null)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pushSubscription.user.name')
                    ->label(__('laravel-pwa::broadcast.fields.user.label'))
                    ->size('xs')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('action_url')
                    ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                    ->state(fn ($record) => $record->resolveContent()->data['url'] ?? null)
                    ->icon('heroicon-o-link')
                    ->url(fn ($record) => $record->resolveContent()->data['url'] ?? null, true)
                    ->color('primary')
                    ->size('xs')
                    ->limit(20)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->action(function ($record): void {
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
                    }),
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
