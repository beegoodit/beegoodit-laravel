<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource\Pages;

use BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBroadcasts extends ListRecords
{
    protected static string $resource = BroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(), // We create broadcasts via the custom page
        ];
    }
}
