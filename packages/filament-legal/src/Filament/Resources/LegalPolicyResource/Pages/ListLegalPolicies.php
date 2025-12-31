<?php

namespace BeeGoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource\Pages;

use BeeGoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLegalPolicies extends ListRecords
{
    protected static string $resource = LegalPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
