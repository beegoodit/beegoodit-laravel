<?php

namespace BeegoodIT\FilamentStreaks\Filament\Clusters;

use BackedEnum;
use BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource;
use BeegoodIT\FilamentStreaks\Models\Streak;
use Filament\Actions\EditAction;
use Filament\Clusters\Cluster as FilamentCluster;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class Cluster extends FilamentCluster implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'streaks';

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $shouldRegisterSubNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 29;

    public function getSubNavigation(): array
    {
        return [];
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public static function canAccess(): bool
    {
        return StreakResource::canAccess();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-streaks::streak.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-streaks::streak.overview.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-streaks::streak.overview.title');
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return __('filament-streaks::streak.navigation_group');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedTable::make(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Streak::query()
                    ->open()
                    ->with(['subject'])
            )
            ->columns([
                TextColumn::make('subject.name')
                    ->label(__('filament-streaks::streak.fields.subject'))
                    ->searchable()
                    ->width('1%'),
                TextColumn::make('current_count')
                    ->label(__('filament-streaks::streak.fields.current'))
                    ->state(fn (Streak $record): int => $this->currentCountFor($record))
                    ->icon(Heroicon::Fire)
                    ->iconPosition(IconPosition::After)
                    ->iconColor('warning')
                    ->alignEnd()
                    ->width('1%'),
                ViewColumn::make('progress')
                    ->label(__('filament-streaks::streak.fields.progress'))
                    ->state(fn (Streak $record): float => $this->relativeProgressPercent($record))
                    ->view('filament-streaks::tables.columns.progress-bar')
                    ->grow(),
            ])
            ->defaultGroup('strategy_identifier')
            ->filters([
                SelectFilter::make('strategy_identifier')
                    ->label(__('filament-streaks::streak.fields.strategy'))
                    ->options(collect(array_keys(config('filament-streaks.strategies', [])))
                        ->mapWithKeys(fn (string $key): array => [$key => $key])
                        ->all()),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Streak $record): string => StreakResource::getUrl('edit', ['record' => $record])),
            ])
            ->defaultSort('start_at', 'desc');
    }

    /**
     * Progress relative to the longest open streak in the same strategy (0–100).
     */
    public function relativeProgressPercent(Streak $record): float
    {
        $baselines = $this->openStreakProgressBaselines();
        $baseline = $baselines[$record->strategy_identifier] ?? null;

        if ($baseline === null || $baseline['max'] <= 0) {
            return 0.0;
        }

        $count = $baseline['counts'][$record->getKey()] ?? $record->currentCount();

        return round(($count / $baseline['max']) * 100, 2);
    }

    public function currentCountFor(Streak $record): int
    {
        $baselines = $this->openStreakProgressBaselines();

        return $baselines[$record->strategy_identifier]['counts'][$record->getKey()]
            ?? $record->currentCount();
    }

    /**
     * @return array<string, array{max: int, counts: array<string, int>}>
     */
    protected function openStreakProgressBaselines(): array
    {
        return once(function (): array {
            return Streak::query()
                ->open()
                ->with(['subject', 'qualifications'])
                ->get()
                ->groupBy('strategy_identifier')
                ->map(function ($streaks): array {
                    $counts = $streaks->mapWithKeys(
                        fn (Streak $streak): array => [$streak->getKey() => $streak->currentCount()]
                    );

                    return [
                        'max' => (int) $counts->max(),
                        'counts' => $counts->all(),
                    ];
                })
                ->all();
        });
    }
}
