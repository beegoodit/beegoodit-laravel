<?php

namespace BeegoodIT\FilamentOpeningTimes\Filament\Resources\ScheduleResource\Pages;

use BeegoodIT\FilamentOpeningTimes\Actions\UpsertScheduleWithSlots;
use BeegoodIT\FilamentOpeningTimes\Filament\Resources\ScheduleResource;
use BeegoodIT\FilamentOpeningTimes\Filament\ScheduleForm;
use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSchedule extends EditRecord
{
    protected static string $resource = ScheduleResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Schedule $record */
        $record = $this->getRecord();

        return ScheduleForm::mutateFormDataForFill($record);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Schedule $record */
        $data = ScheduleForm::ensureOpenableColumnsOnRoot($data);
        $openableType = (string) ($data['openable_type'] ?? $record->openable_type);
        $openableId = (string) ($data['openable_id'] ?? $record->openable_id);

        return UpsertScheduleWithSlots::run(
            $record,
            $openableType,
            $openableId,
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
