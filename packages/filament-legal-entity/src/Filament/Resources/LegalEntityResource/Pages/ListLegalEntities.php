<?php

namespace BeegoodIT\FilamentLegalEntity\Filament\Resources\LegalEntityResource\Pages;

use BeegoodIT\FilamentLegalEntity\Filament\Resources\LegalEntityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLegalEntities extends ListRecords
{
    protected static string $resource = LegalEntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
