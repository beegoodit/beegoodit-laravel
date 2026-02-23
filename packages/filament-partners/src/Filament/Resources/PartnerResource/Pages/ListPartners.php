<?php

namespace BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource\Pages;

use BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource;
use Filament\Resources\Pages\ListRecords;

class ListPartners extends ListRecords
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
