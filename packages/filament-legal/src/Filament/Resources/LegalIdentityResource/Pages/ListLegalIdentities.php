<?php

namespace BeegoodIT\FilamentLegal\Filament\Resources\LegalIdentityResource\Pages;

use BeegoodIT\FilamentLegal\Filament\Resources\LegalIdentityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLegalIdentities extends ListRecords
{
    protected static string $resource = LegalIdentityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('filament-legal::messages.Create Legal Identity')),
        ];
    }
}
