<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources;

use BackedEnum;
use BeegoodIT\FilamentStreaks\Enums\OperationRunStatus;
use BeegoodIT\FilamentStreaks\Filament\Clusters\Cluster;
use BeegoodIT\FilamentStreaks\Filament\Concerns\HasNavigationBadgeCount;
use BeegoodIT\FilamentStreaks\Filament\Concerns\RegistersInMainSidebar;
use BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource\Pages\CreateHistoryRun;
use BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource\Pages\ListHistoryRuns;
use BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource\Pages\ViewHistoryRun;
use BeegoodIT\FilamentStreaks\Models\OperationRun;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HistoryResource extends Resource
{
    use HasNavigationBadgeCount, RegistersInMainSidebar;

    protected static ?string $model = OperationRun::class;

    protected static ?string $cluster = Cluster::class;

    protected static ?string $slug = 'history';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?int $navigationSort = 33;

    public static function getModelLabel(): string
    {
        return __('filament-streaks::history.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-streaks::history.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-streaks::history.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-streaks::history.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('from')
                    ->label(__('filament-streaks::history.fields.from'))
                    ->required()
                    ->columnSpan(1),
                DatePicker::make('to')
                    ->label(__('filament-streaks::history.fields.to'))
                    ->required()
                    ->afterOrEqual('from')
                    ->columnSpan(1),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('status')
                    ->label(__('filament-streaks::history.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (OperationRunStatus $state): string => __("filament-streaks::history.status.{$state->value}"))
                    ->color(fn (OperationRunStatus $state): string => match ($state) {
                        OperationRunStatus::Queued => 'gray',
                        OperationRunStatus::Running => 'info',
                        OperationRunStatus::Succeeded => 'success',
                        OperationRunStatus::Failed => 'danger',
                    }),
                TextEntry::make('requestedBy.name')
                    ->label(__('filament-streaks::history.fields.requested_by'))
                    ->placeholder('—'),
                TextEntry::make('params.from')
                    ->label(__('filament-streaks::history.fields.from')),
                TextEntry::make('params.to')
                    ->label(__('filament-streaks::history.fields.to')),
                TextEntry::make('started_at')
                    ->label(__('filament-streaks::history.fields.started_at'))
                    ->dateTime()
                    ->placeholder('—'),
                TextEntry::make('finished_at')
                    ->label(__('filament-streaks::history.fields.finished_at'))
                    ->dateTime()
                    ->placeholder('—'),
                KeyValueEntry::make('result')
                    ->label(__('filament-streaks::history.fields.result'))
                    ->columnSpanFull()
                    ->visible(fn (OperationRun $record): bool => filled($record->result)),
                TextEntry::make('error')
                    ->label(__('filament-streaks::history.fields.error'))
                    ->placeholder('—')
                    ->columnSpanFull()
                    ->visible(fn (OperationRun $record): bool => filled($record->error)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->label(__('filament-streaks::history.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (OperationRunStatus $state): string => __("filament-streaks::history.status.{$state->value}"))
                    ->color(fn (OperationRunStatus $state): string => match ($state) {
                        OperationRunStatus::Queued => 'gray',
                        OperationRunStatus::Running => 'info',
                        OperationRunStatus::Succeeded => 'success',
                        OperationRunStatus::Failed => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('params.from')
                    ->label(__('filament-streaks::history.fields.from')),
                TextColumn::make('params.to')
                    ->label(__('filament-streaks::history.fields.to')),
                TextColumn::make('requestedBy.name')
                    ->label(__('filament-streaks::history.fields.requested_by'))
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label(__('filament-streaks::history.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->label(__('filament-streaks::history.fields.finished_at'))
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->history()
            ->with(['requestedBy']);
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHistoryRuns::route('/'),
            'create' => CreateHistoryRun::route('/create'),
            'view' => ViewHistoryRun::route('/{record}'),
        ];
    }
}
