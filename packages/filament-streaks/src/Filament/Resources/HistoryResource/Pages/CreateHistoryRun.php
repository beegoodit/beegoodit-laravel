<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource\Pages;

use BeegoodIT\FilamentStreaks\Actions\CreateStreakHistoryRun;
use BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CreateHistoryRun extends CreateRecord
{
    protected static string $resource = HistoryResource::class;

    protected static bool $canCreateAnother = false;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        return CreateStreakHistoryRun::run(
            from: Carbon::parse($data['from'])->startOfDay(),
            to: Carbon::parse($data['to'])->endOfDay(),
            requestedBy: auth()->user(),
        );
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament-streaks::history.notifications.queued_title'))
            ->body(__('filament-streaks::history.notifications.queued_body'));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
