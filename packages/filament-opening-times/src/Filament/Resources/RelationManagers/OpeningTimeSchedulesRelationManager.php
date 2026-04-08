<?php

namespace BeegoodIT\FilamentOpeningTimes\Filament\Resources\RelationManagers;

use BeegoodIT\FilamentOpeningTimes\Actions\UpsertScheduleWithSlots;
use BeegoodIT\FilamentOpeningTimes\Filament\ScheduleForm;
use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OpeningTimeSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'openingTimeSchedules';

    protected static bool $isLazy = false;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament-opening-times::opening_schedule.relation_title');
    }

    public function form(Schema $schema): Schema
    {
        return ScheduleForm::configure($schema, $this->ownerRecord);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('timezone')
                    ->label(__('filament-opening-times::opening_schedule.timezone_label')),
                TextColumn::make('active_from')
                    ->label(__('filament-opening-times::opening_schedule.active_from_label'))
                    ->dateTime(),
                TextColumn::make('active_to')
                    ->label(__('filament-opening-times::opening_schedule.active_to_label'))
                    ->dateTime(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(Schedule::class)
                    ->schema(fn (Schema $schema): Schema => ScheduleForm::configure($schema, $this->ownerRecord))
                    ->fillForm(fn (): array => ScheduleForm::defaultCreateState($this->ownerRecord))
                    ->using(function (array $data, \Filament\Actions\Contracts\HasActions&HasSchemas $livewire): Schedule {
                        /** @var \Filament\Resources\RelationManagers\RelationManager $livewire */
                        $owner = $livewire->ownerRecord;
                        $data = ScheduleForm::ensureOpenableColumnsOnRoot($data);

                        return UpsertScheduleWithSlots::run(
                            null,
                            $owner->getMorphClass(),
                            (string) $owner->getKey(),
                            (string) $data['timezone'],
                            $data['active_from'],
                            $data['active_to'],
                            ScheduleForm::slotsFromDayFields($data),
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema(fn (Schema $schema): Schema => ScheduleForm::configure($schema, $this->ownerRecord))
                    ->fillForm(fn (Schedule $record): array => ScheduleForm::mutateFormDataForFill($record))
                    ->using(function (array $data, \Filament\Actions\Contracts\HasActions&HasSchemas $livewire, Model $record): void {
                        /** @var Schedule $record */
                        $data = ScheduleForm::ensureOpenableColumnsOnRoot($data);
                        UpsertScheduleWithSlots::run(
                            $record,
                            $record->openable_type,
                            (string) $record->openable_id,
                            (string) $data['timezone'],
                            $data['active_from'],
                            $data['active_to'],
                            ScheduleForm::slotsFromDayFields($data),
                        );
                    }),
                DeleteAction::make(),
            ]);
    }
}
