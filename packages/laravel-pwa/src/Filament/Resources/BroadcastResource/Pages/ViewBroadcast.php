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
            // Actions\DeleteAction::make(),
        ];
    }
}
