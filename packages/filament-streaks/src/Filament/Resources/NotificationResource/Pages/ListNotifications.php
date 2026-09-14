<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\NotificationResource\Pages;

use BeegoodIT\FilamentStreaks\Filament\Resources\NotificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
