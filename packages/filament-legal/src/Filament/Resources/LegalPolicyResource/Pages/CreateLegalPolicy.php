<?php

namespace BeeGoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource\Pages;

use BeeGoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource;
use BeeGoodIT\FilamentLegal\Models\LegalPolicy;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalPolicy extends CreateRecord
{
    protected static string $resource = LegalPolicyResource::class;

    protected function afterCreate(): void
    {
        if ($this->record->is_active) {
            LegalPolicy::where('type', $this->record->type)
                ->where('id', '!=', $this->record->id)
                ->update(['is_active' => false]);
        }
    }
}
