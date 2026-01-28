<?php

namespace BeegoodIT\FilamentTenancyDomains\Filament\Resources\DomainResource\Pages;

use BeegoodIT\FilamentTenancyDomains\Filament\Resources\DomainResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDomains extends ListRecords
{
    protected static string $resource = DomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
