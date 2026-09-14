<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources;

use BackedEnum;
use BeegoodIT\FilamentStreaks\Filament\Clusters\Cluster;
use BeegoodIT\FilamentStreaks\Filament\Concerns\HasNavigationBadgeCount;
use BeegoodIT\FilamentStreaks\Filament\Concerns\RegistersInMainSidebar;
use BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource\Pages\CreateQualification;
use BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource\Pages\EditQualification;
use BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource\Pages\ListQualifications;
use BeegoodIT\FilamentStreaks\Models\Qualification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QualificationResource extends Resource
{
    use HasNavigationBadgeCount, RegistersInMainSidebar;

    protected static ?string $model = Qualification::class;

    protected static ?string $cluster = Cluster::class;

    protected static ?string $slug = 'qualifications';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static ?int $navigationSort = 31;

    public static function getModelLabel(): string
    {
        return __('filament-streaks::qualification.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-streaks::qualification.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-streaks::qualification.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-streaks::qualification.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('streak_id')
                    ->label(__('filament-streaks::qualification.fields.streak'))
                    ->relationship('streak', 'strategy_identifier')
                    ->getOptionLabelFromRecordUsing(fn (Streak $record): string => "{$record->strategy_identifier} — {$record->subject?->name}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                ...static::eventFormComponents(),
                DateTimePicker::make('happened_at')
                    ->label(__('filament-streaks::qualification.fields.happened_at'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('streak.strategy_identifier')
                    ->label(__('filament-streaks::qualification.fields.streak'))
                    ->sortable(),
                TextColumn::make('event_type')
                    ->label(__('filament-streaks::qualification.fields.event'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->toggleable(),
                TextColumn::make('happened_at')
                    ->label(__('filament-streaks::qualification.fields.happened_at'))
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
        return parent::getEloquentQuery()->with(['streak.subject', 'event']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQualifications::route('/'),
            'create' => CreateQualification::route('/create'),
            'edit' => EditQualification::route('/{record}/edit'),
        ];
    }

    /**
     * @return list<\Filament\Forms\Components\Component>
     */
    public static function eventFormComponents(): array
    {
        $eventModels = config('filament-streaks.admin_event_models', []);

        if ($eventModels !== []) {
            return [
                MorphToSelect::make('event')
                    ->label(__('filament-streaks::qualification.fields.event'))
                    ->types(collect($eventModels)
                        ->map(function (string $model): MorphToSelect\Type {
                            $titleAttribute = config("filament-streaks.event_title_attributes.{$model}", 'name');

                            return MorphToSelect\Type::make($model)->titleAttribute($titleAttribute);
                        })
                        ->all())
                    ->columnSpanFull(),
            ];
        }

        return [
            TextInput::make('event_type')
                ->label(__('filament-streaks::qualification.fields.event_type'))
                ->required()
                ->columnSpan(1),
            TextInput::make('event_id')
                ->label(__('filament-streaks::qualification.fields.event_id'))
                ->required()
                ->uuid()
                ->columnSpan(1),
        ];
    }
}
