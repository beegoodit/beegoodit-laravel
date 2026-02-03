<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources\MessageResource\Pages;

use BeegoodIT\LaravelPwa\Filament\Resources\MessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMessage extends EditRecord
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            Actions\Action::make('resend')
                ->label(__('laravel-pwa::broadcast.buttons.resend'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn ($record): bool => $record->delivery_status->canTransitionTo(\BeegoodIT\LaravelPwa\States\Messages\Pending::class) && ! $record->delivery_status->equals(\BeegoodIT\LaravelPwa\States\Messages\OnHold::class) && ! $record->delivery_status->equals(\BeegoodIT\LaravelPwa\States\Messages\Pending::class))
                ->action(function ($record): void {
                    $record->resend();

                    dispatch(new \BeegoodIT\LaravelPwa\Notifications\Jobs\SendMessageJob($record))
                        ->onQueue(config('pwa.notifications.queue', 'default'));

                    \Filament\Notifications\Notification::make()
                        ->title(__('laravel-pwa::broadcast.notifications.requeued.title'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
