<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource\Pages;

use BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHistoryRuns extends ListRecords
{
    protected static string $resource = HistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
