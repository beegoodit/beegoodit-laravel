<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources;

use BackedEnum;
use BeegoodIT\FilamentStreaks\Filament\Clusters\Cluster;
use BeegoodIT\FilamentStreaks\Filament\Concerns\HasNavigationBadgeCount;
use BeegoodIT\FilamentStreaks\Filament\Concerns\RegistersInMainSidebar;
use BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource\Pages\CreateStreak;
use BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource\Pages\EditStreak;
use BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource\Pages\ListStreaks;
use BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource\RelationManagers\NotificationsRelationManager;
use BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource\RelationManagers\QualificationsRelationManager;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Support\StrategyResolver;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StreakResource extends Resource
{
    use HasNavigationBadgeCount, RegistersInMainSidebar;

    protected static ?string $model = Streak::class;

    protected static ?string $cluster = Cluster::class;

    protected static ?string $slug = 'streaks';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return __('filament-streaks::streak.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-streaks::streak.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-streaks::streak.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-streaks::streak.plural');
    }

    public static function form(Schema $schema): Schema
    {
        $strategies = array_keys(config('filament-streaks.strategies', []));

        return $schema
            ->columns(2)
            ->components([
                Select::make('strategy_identifier')
                    ->label(__('filament-streaks::streak.fields.strategy'))
                    ->options(collect($strategies)->mapWithKeys(fn (string $key): array => [$key => $key]))
                    ->required()
                    ->live()
                    ->columnSpanFull(),
                MorphToSelect::make('subject')
                    ->label(__('filament-streaks::streak.fields.subject'))
                    ->types(fn (Get $get): array => static::subjectTypesForStrategy($get('strategy_identifier')))
                    ->visible(fn (Get $get): bool => filled($get('strategy_identifier')))
                    ->required()
                    ->searchable()
                    ->columnSpanFull(),
                MorphToSelect::make('scope')
                    ->label(__('filament-streaks::streak.fields.scope'))
                    ->types(fn (Get $get): array => static::scopeTypesForStrategy($get('strategy_identifier')))
                    ->visible(fn (Get $get): bool => filled($get('strategy_identifier')) && static::strategyRequiresScope($get('strategy_identifier')))
                    ->required(fn (Get $get): bool => static::strategyRequiresScope($get('strategy_identifier')))
                    ->columnSpanFull(),
                DatePicker::make('start_at')
                    ->label(__('filament-streaks::streak.fields.start_at'))
                    ->required()
                    ->columnSpan(1),
                DatePicker::make('end_at')
                    ->label(__('filament-streaks::streak.fields.end_at'))
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('strategy_identifier')
                    ->label(__('filament-streaks::streak.fields.strategy'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject.name')
                    ->label(__('filament-streaks::streak.fields.subject'))
                    ->searchable(),
                TextColumn::make('scope_type')
                    ->label(__('filament-streaks::streak.fields.scope'))
                    ->formatStateUsing(fn (?string $state, Streak $record): string => $record->scope?->getKey()
                        ? class_basename((string) $state).' #'.$record->scope->getKey()
                        : '—')
                    ->toggleable(),
                TextColumn::make('start_at')
                    ->label(__('filament-streaks::streak.fields.start_at'))
                    ->date()
                    ->sortable(),
                TextColumn::make('end_at')
                    ->label(__('filament-streaks::streak.fields.end_at'))
                    ->date()
                    ->placeholder(__('filament-streaks::streak.status.open'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('strategy_identifier')
                    ->label(__('filament-streaks::streak.fields.strategy'))
                    ->options(collect(array_keys(config('filament-streaks.strategies', [])))
                        ->mapWithKeys(fn (string $key): array => [$key => $key])
                        ->all()),
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
        return parent::getEloquentQuery()->with(['subject', 'scope']);
    }

    public static function getRelations(): array
    {
        return [
            QualificationsRelationManager::class,
            NotificationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStreaks::route('/'),
            'create' => CreateStreak::route('/create'),
            'edit' => EditStreak::route('/{record}/edit'),
        ];
    }

    /**
     * @return list<MorphToSelect\Type>
     */
    protected static function subjectTypesForStrategy(?string $strategyIdentifier): array
    {
        if (! filled($strategyIdentifier)) {
            return [];
        }

        try {
            $strategy = app(StrategyResolver::class)->resolve($strategyIdentifier);
        } catch (\Throwable) {
            return [];
        }

        $subjectType = $strategy->subjectType();
        $titleAttribute = config("filament-streaks.subject_title_attributes.{$subjectType}", 'name');

        return [
            MorphToSelect\Type::make($subjectType)
                ->titleAttribute($titleAttribute)
                ->modifyOptionsQueryUsing(function (Builder $query) use ($strategy): Builder {
                    $subjects = $strategy->subjects();
                    $key = $subjects->getModel()->getQualifiedKeyName();

                    return $query->whereIn($query->getModel()->getQualifiedKeyName(), $subjects->select($key));
                }),
        ];
    }

    /**
     * @return list<MorphToSelect\Type>
     */
    protected static function scopeTypesForStrategy(?string $strategyIdentifier): array
    {
        if (! filled($strategyIdentifier)) {
            return [];
        }

        try {
            $scopeType = app(StrategyResolver::class)->resolve($strategyIdentifier)->scopeType();
        } catch (\Throwable) {
            return [];
        }

        if ($scopeType === null) {
            return [];
        }

        $titleAttribute = config("filament-streaks.scope_title_attributes.{$scopeType}", 'name');

        return [
            MorphToSelect\Type::make($scopeType)->titleAttribute($titleAttribute),
        ];
    }

    protected static function strategyRequiresScope(?string $strategyIdentifier): bool
    {
        if (! filled($strategyIdentifier)) {
            return false;
        }

        try {
            return app(StrategyResolver::class)->resolve($strategyIdentifier)->scopeType() !== null;
        } catch (\Throwable) {
            return false;
        }
    }
}
