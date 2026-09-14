<?php

namespace BeegoodIT\FilamentLegalEntity\Filament\Resources\LegalEntityResource\Pages;

use BeegoodIT\FilamentLegalEntity\Filament\Resources\LegalEntityResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalEntity extends CreateRecord
{
    protected static string $resource = LegalEntityResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();

        if ($tenant !== null) {
            $data['owner_type'] = $tenant->getMorphClass();
            $data['owner_id'] = (string) $tenant->getKey();
        } else {
            $data['owner_type'] ??= null;
            $data['owner_id'] ??= null;
        }

        return $data;
    }
}
