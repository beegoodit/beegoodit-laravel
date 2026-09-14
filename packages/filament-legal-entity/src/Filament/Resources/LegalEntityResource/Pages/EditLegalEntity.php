<?php

namespace BeegoodIT\FilamentLegalEntity\Filament\Resources\LegalEntityResource\Pages;

use BeegoodIT\FilamentLegalEntity\Filament\Resources\LegalEntityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLegalEntity extends EditRecord
{
    protected static string $resource = LegalEntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
