<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource\RelationManagers;

use BeegoodIT\FilamentStreaks\Models\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'notifications';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament-streaks::notification.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('kind')
                    ->label(__('filament-streaks::notification.fields.kind'))
                    ->options([
                        Notification::KIND_AT_RISK => __('filament-streaks::notification.kinds.at_risk'),
                        Notification::KIND_BROKEN => __('filament-streaks::notification.kinds.broken'),
                    ])
                    ->required()
                    ->columnSpan(1),
                TextInput::make('period_key')
                    ->label(__('filament-streaks::notification.fields.period_key'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),
                DateTimePicker::make('sent_at')
                    ->label(__('filament-streaks::notification.fields.sent_at'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kind')
                    ->label(__('filament-streaks::notification.fields.kind'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("filament-streaks::notification.kinds.{$state}")),
                TextColumn::make('period_key')
                    ->label(__('filament-streaks::notification.fields.period_key'))
                    ->searchable(),
                TextColumn::make('sent_at')
                    ->label(__('filament-streaks::notification.fields.sent_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sent_at', 'desc');
    }
}
