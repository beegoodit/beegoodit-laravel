<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources\MessageResource\Pages;

use BeegoodIT\LaravelPwa\Filament\Resources\MessageResource;
use Filament\Resources\Pages\ListRecords;

class ListMessages extends ListRecords
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
