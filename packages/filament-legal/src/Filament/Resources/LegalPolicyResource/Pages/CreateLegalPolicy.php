<?php

namespace BeegoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource\Pages;

use BeegoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource;
use BeegoodIT\FilamentLegal\Models\LegalPolicy;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalPolicy extends CreateRecord
{
    protected static string $resource = LegalPolicyResource::class;

    public function getTitle(): string
    {
        return __('filament-legal::messages.Create Legal Policy');
    }

    public function getBreadcrumb(): string
    {
        return __('filament-legal::messages.Create');
    }

    protected function afterCreate(): void
    {
        if ($this->record->is_active) {
            LegalPolicy::where('type', $this->record->type)
                ->where('id', '!=', $this->record->id)
                ->update(['is_active' => false]);
        }
    }
}
