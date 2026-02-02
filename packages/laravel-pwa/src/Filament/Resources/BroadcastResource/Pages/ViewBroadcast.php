<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource\Pages;

use BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBroadcast extends ViewRecord
{
    protected static string $resource = BroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('resend')
                ->label(__('laravel-pwa::broadcast.buttons.resend'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (\BeegoodIT\LaravelPwa\Models\Notifications\Broadcast $record): void {
                    $record->update(['status' => 'pending']);

                    dispatch(new \BeegoodIT\LaravelPwa\Notifications\Jobs\ProcessBroadcastJob($record))
                        ->onQueue(config('pwa.notifications.queue', 'default'));

                    \Filament\Notifications\Notification::make()
                        ->title(__('laravel-pwa::broadcast.notifications.requeued.title'))
                        ->success()
                        ->send();
                })
                ->visible(fn (\BeegoodIT\LaravelPwa\Models\Notifications\Broadcast $record): bool => in_array($record->status, ['completed', 'failed'])),
        ];
    }
}
