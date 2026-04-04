<?php

namespace BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource\Pages;

use BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEntryLink extends EditRecord
{
    protected static string $resource = EntryLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
