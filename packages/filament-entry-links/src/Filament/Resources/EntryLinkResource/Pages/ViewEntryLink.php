<?php

namespace BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource\Pages;

use BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEntryLink extends ViewRecord
{
    protected static string $resource = EntryLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
