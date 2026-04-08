<?php

namespace BeegoodIT\FilamentOpeningTimes\Filament\Resources;

use BeegoodIT\FilamentOpeningTimes\Filament\Resources\ScheduleResource\Pages\CreateSchedule;
use BeegoodIT\FilamentOpeningTimes\Filament\Resources\ScheduleResource\Pages\EditSchedule;
use BeegoodIT\FilamentOpeningTimes\Filament\Resources\ScheduleResource\Pages\ListSchedules;
use BeegoodIT\FilamentOpeningTimes\Filament\ScheduleForm;
use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 45;

    public static function getModelLabel(): string
    {
        return __('filament-opening-times::opening_schedule.name');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-opening-times::opening_schedule.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-opening-times::opening_schedule.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return ScheduleForm::configure($schema, ownerRecord: null);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('openable.name')
                    ->label(__('filament-opening-times::opening_schedule.openable_label'))
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('timezone')
                    ->label(__('filament-opening-times::opening_schedule.timezone_label'))
                    ->toggleable(),
                TextColumn::make('active_from')
                    ->label(__('filament-opening-times::opening_schedule.active_from_label'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('active_to')
                    ->label(__('filament-opening-times::opening_schedule.active_to_label'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('active_from', 'desc')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['openable']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchedules::route('/'),
            'create' => CreateSchedule::route('/create'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->with(['slots']);
    }

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record);
    }
}
