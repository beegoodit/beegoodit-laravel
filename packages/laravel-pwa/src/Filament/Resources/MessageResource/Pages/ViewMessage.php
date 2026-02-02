<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources\MessageResource\Pages;

use BeegoodIT\LaravelPwa\Filament\Resources\MessageResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewMessage extends ViewRecord
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resend')
                ->label(__('laravel-pwa::broadcast.buttons.resend'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (Message $record): void {
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
                }),
        ];
    }
}
