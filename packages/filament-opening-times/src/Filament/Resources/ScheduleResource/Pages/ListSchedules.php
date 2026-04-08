<?php

namespace BeegoodIT\FilamentOpeningTimes\Filament\Resources\ScheduleResource\Pages;

use BeegoodIT\FilamentOpeningTimes\Filament\Resources\ScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
