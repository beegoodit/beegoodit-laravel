<?php

namespace BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource\Pages;

use BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEntryLinks extends ListRecords
{
    protected static string $resource = EntryLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
