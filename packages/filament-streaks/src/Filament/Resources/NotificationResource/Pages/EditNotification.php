<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\NotificationResource\Pages;

use BeegoodIT\FilamentStreaks\Filament\Resources\NotificationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNotification extends EditRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
