<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource\Pages;

use BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStreaks extends ListRecords
{
    protected static string $resource = StreakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
