<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource\Pages;

use BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQualifications extends ListRecords
{
    protected static string $resource = QualificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
