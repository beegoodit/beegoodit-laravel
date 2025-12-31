<?php

namespace BeeGoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource\Pages;

use BeeGoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource;
use BeeGoodIT\FilamentLegal\Models\LegalPolicy;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLegalPolicy extends EditRecord
{
    protected static string $resource = LegalPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->is_active) {
            LegalPolicy::where('type', $this->record->type)
                ->where('id', '!=', $this->record->id)
                ->update(['is_active' => false]);
        }
    }
}
