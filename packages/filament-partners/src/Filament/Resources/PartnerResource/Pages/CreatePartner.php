<?php

namespace BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource\Pages;

use BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource;
use BeegoodIT\FilamentPartners\Models\Partner;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreatePartner extends CreateRecord
{
    protected static string $resource = PartnerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();

        if ($tenant !== null) {
            $data['partnerable_type'] = $tenant->getMorphClass();
            $data['partnerable_id'] = $tenant->getKey();
        } else {
            $data['partnerable_type'] = $data['partnerable_type'] ?? null;
            $data['partnerable_id'] = $data['partnerable_id'] ?? null;
        }

        // Position is set by Spatie SortableTrait (sort_when_creating) scoped via buildSortQuery()
        return $data;
    }
}
