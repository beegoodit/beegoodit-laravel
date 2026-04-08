<?php

namespace BeegoodIT\FilamentOpeningTimes\Filament\Resources\ScheduleResource\Pages;

use BeegoodIT\FilamentOpeningTimes\Actions\UpsertScheduleWithSlots;
use BeegoodIT\FilamentOpeningTimes\Filament\Resources\ScheduleResource;
use BeegoodIT\FilamentOpeningTimes\Filament\ScheduleForm;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ScheduleForm::ensureOpenableColumnsOnRoot($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $data = ScheduleForm::ensureOpenableColumnsOnRoot($data);

        return UpsertScheduleWithSlots::run(
            null,
            (string) $data['openable_type'],
            (string) $data['openable_id'],
            (string) $data['timezone'],
            $data['active_from'],
            $data['active_to'],
            ScheduleForm::slotsFromDayFields($data),
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
