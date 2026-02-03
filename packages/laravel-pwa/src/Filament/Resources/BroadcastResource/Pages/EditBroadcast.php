<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource\Pages;

use BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBroadcast extends EditRecord
{
    protected static string $resource = BroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            Actions\Action::make('hold')
                ->label(__('laravel-pwa::notifications.messages.actions.hold'))
                ->icon('heroicon-o-pause-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn ($record): bool => $record->status->canTransitionTo(\BeegoodIT\LaravelPwa\States\Broadcasts\OnHold::class))
                ->action(function ($record): void {
                    $record->hold();

                    \Filament\Notifications\Notification::make()
                        ->title(__('laravel-pwa::broadcast.notifications.held.title'))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('release')
                ->label(__('laravel-pwa::notifications.messages.actions.release'))
                ->icon('heroicon-o-play-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record): bool => $record->status->canTransitionTo(\BeegoodIT\LaravelPwa\States\Broadcasts\Pending::class) && $record->status->equals(\BeegoodIT\LaravelPwa\States\Broadcasts\OnHold::class))
                ->action(function ($record): void {
                    $record->release();

                    \Filament\Notifications\Notification::make()
                        ->title(__('laravel-pwa::broadcast.notifications.released.title'))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('resend')
                ->label(__('laravel-pwa::broadcast.buttons.resend'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn ($record): bool => $record->status->canTransitionTo(\BeegoodIT\LaravelPwa\States\Broadcasts\Pending::class) && ! $record->status->equals(\BeegoodIT\LaravelPwa\States\Broadcasts\OnHold::class) && ! $record->status->equals(\BeegoodIT\LaravelPwa\States\Broadcasts\Pending::class))
                ->action(function ($record): void {
                    $record->resend();

                    dispatch(new \BeegoodIT\LaravelPwa\Notifications\Jobs\ProcessBroadcastJob($record))
                        ->onQueue(config('pwa.notifications.queue', 'default'));

                    \Filament\Notifications\Notification::make()
                        ->title(__('laravel-pwa::broadcast.notifications.requeued.title'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
