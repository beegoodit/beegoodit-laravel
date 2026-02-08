<?php

namespace BeegoodIT\FilamentLegal\Filament\Resources\LegalIdentityResource\Pages;

use BeegoodIT\FilamentLegal\Filament\Resources\LegalIdentityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLegalIdentity extends ViewRecord
{
    protected static string $resource = LegalIdentityResource::class;

    public function getTitle(): string
    {
        return __('filament-legal::messages.View Legal Identity');
    }

    public function getBreadcrumb(): string
    {
        return __('filament-legal::messages.View');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
