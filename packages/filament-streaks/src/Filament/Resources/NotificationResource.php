<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources;

use BackedEnum;
use BeegoodIT\FilamentStreaks\Filament\Clusters\Cluster;
use BeegoodIT\FilamentStreaks\Filament\Concerns\HasNavigationBadgeCount;
use BeegoodIT\FilamentStreaks\Filament\Concerns\RegistersInMainSidebar;
use BeegoodIT\FilamentStreaks\Filament\Resources\NotificationResource\Pages\CreateNotification;
use BeegoodIT\FilamentStreaks\Filament\Resources\NotificationResource\Pages\EditNotification;
use BeegoodIT\FilamentStreaks\Filament\Resources\NotificationResource\Pages\ListNotifications;
use BeegoodIT\FilamentStreaks\Models\Notification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationResource extends Resource
{
    use HasNavigationBadgeCount, RegistersInMainSidebar;

    protected static ?string $model = Notification::class;

    protected static ?string $cluster = Cluster::class;

    protected static ?string $slug = 'notifications';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?int $navigationSort = 32;

    public static function getModelLabel(): string
    {
        return __('filament-streaks::notification.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-streaks::notification.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-streaks::notification.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-streaks::notification.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('streak_id')
                    ->label(__('filament-streaks::notification.fields.streak'))
                    ->relationship('streak', 'strategy_identifier')
                    ->getOptionLabelFromRecordUsing(fn (Streak $record): string => "{$record->strategy_identifier} — {$record->subject?->name}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                Select::make('kind')
                    ->label(__('filament-streaks::notification.fields.kind'))
                    ->options([
                        Notification::KIND_AT_RISK => __('filament-streaks::notification.kinds.at_risk'),
                        Notification::KIND_BROKEN => __('filament-streaks::notification.kinds.broken'),
                    ])
                    ->required()
                    ->columnSpan(1),
                \Filament\Forms\Components\TextInput::make('period_key')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('streak.strategy_identifier')
                    ->label(__('filament-streaks::notification.fields.streak'))
                    ->sortable(),
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
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['streak.subject']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotifications::route('/'),
            'create' => CreateNotification::route('/create'),
            'edit' => EditNotification::route('/{record}/edit'),
        ];
    }
}
