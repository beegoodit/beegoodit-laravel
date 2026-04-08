<?php

namespace BeegoodIT\FilamentOpeningTimes\Filament;

use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use DateTimeZone;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

final class ScheduleForm
{
    /**
     * MorphToSelect nests `openable_type` / `openable_id` under the `openable` fieldset; merge onto the root for Actions.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function ensureOpenableColumnsOnRoot(array $data): array
    {
        if (isset($data['openable']) && is_array($data['openable'])) {
            if (array_key_exists('openable_type', $data['openable'])) {
                $data['openable_type'] = $data['openable']['openable_type'];
            }
            if (array_key_exists('openable_id', $data['openable'])) {
                $data['openable_id'] = $data['openable']['openable_id'];
            }
        }

        return $data;
    }

    /**
     * @return array<int, array{day_of_week: int, opens_at: mixed, closes_at: mixed}>
     */
    public static function slotsFromDayFields(array $data): array
    {
        $slots = [];
        for ($d = 0; $d < 7; $d++) {
            if (! empty($data["day_{$d}_closed"])) {
                continue;
            }
            $intervals = $data["day_{$d}_intervals"] ?? [];
            foreach ($intervals as $interval) {
                if (empty($interval['opens_at']) || empty($interval['closes_at'])) {
                    continue;
                }
                $slots[] = [
                    'day_of_week' => $d,
                    'opens_at' => $interval['opens_at'],
                    'closes_at' => $interval['closes_at'],
                ];
            }
        }

        return $slots;
    }

    /**
     * @return array<string, mixed>
     */
    public static function mutateFormDataForFill(Schedule $record): array
    {
        $record->load('slots');
        $data = $record->only(['timezone', 'active_from', 'active_to', 'openable_type', 'openable_id']);
        $data['openable'] = [
            'openable_type' => $record->openable_type,
            'openable_id' => $record->openable_id,
        ];

        for ($d = 0; $d < 7; $d++) {
            $daySlots = $record->slots->where('day_of_week', $d)->values();
            $data["day_{$d}_closed"] = $daySlots->isEmpty();
            $data["day_{$d}_intervals"] = $daySlots->map(fn ($s): array => [
                'opens_at' => $s->opens_at,
                'closes_at' => $s->closes_at,
            ])->all();
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultCreateState(?Model $owner = null): array
    {
        $data = [
            'timezone' => config('app.timezone', 'UTC'),
            'active_from' => Date::parse('1970-01-01 00:00:00'),
            'active_to' => Date::parse('9999-12-31 23:59:59'),
        ];

        for ($d = 0; $d < 7; $d++) {
            $data["day_{$d}_closed"] = true;
            $data["day_{$d}_intervals"] = [];
        }

        if ($owner !== null) {
            $data['openable_type'] = $owner->getMorphClass();
            $data['openable_id'] = $owner->getKey();
        }

        return $data;
    }

    public static function configure(Schema $schema, ?Model $ownerRecord = null): Schema
    {
        $models = config('filament-opening-times.openable_models', []);
        $tzList = DateTimeZone::listIdentifiers();

        $components = [];

        if ($ownerRecord === null && $models !== []) {
            $components[] = MorphToSelect::make('openable')
                ->label(__('filament-opening-times::opening_schedule.openable_label'))
                ->types(collect($models)
                    ->map(fn (string $model): MorphToSelect\Type => MorphToSelect\Type::make($model)->titleAttribute('name'))
                    ->all())
                ->columnSpanFull()
                ->required();
        }

        $components[] = Select::make('timezone')
            ->label(__('filament-opening-times::opening_schedule.timezone_label'))
            ->options(array_combine($tzList, $tzList))
            ->searchable()
            ->required()
            ->columnSpanFull();

        $components[] = DateTimePicker::make('active_from')
            ->label(__('filament-opening-times::opening_schedule.active_from_label'))
            ->required()
            ->default(Date::parse('1970-01-01 00:00:00'))
            ->columnSpan(1);

        $components[] = DateTimePicker::make('active_to')
            ->label(__('filament-opening-times::opening_schedule.active_to_label'))
            ->required()
            ->default(Date::parse('9999-12-31 23:59:59'))
            ->columnSpan(1);

        for ($d = 0; $d < 7; $d++) {
            $components[] = Section::make(__('filament-opening-times::days.'.(string) $d))
                ->schema([
                    Toggle::make("day_{$d}_closed")
                        ->label(__('filament-opening-times::opening_schedule.closed_label'))
                        ->default(true)
                        ->live(),
                    Repeater::make("day_{$d}_intervals")
                        ->label(__('filament-opening-times::opening_schedule.intervals_label'))
                        ->schema([
                            TimePicker::make('opens_at')
                                ->label(__('filament-opening-times::opening_schedule.opens_at_label'))
                                ->seconds(false)
                                ->required()
                                ->live(onBlur: true),
                            TimePicker::make('closes_at')
                                ->label(__('filament-opening-times::opening_schedule.closes_at_label'))
                                ->seconds(false)
                                ->required()
                                ->live(onBlur: true)
                                ->helperText(function (Get $get): ?string {
                                    $o = $get('opens_at');
                                    $c = $get('closes_at');
                                    if ($o === null || $c === null) {
                                        return null;
                                    }
                                    $os = $o instanceof \Carbon\CarbonInterface ? $o->format('H:i:s') : (string) $o;
                                    $cs = $c instanceof \Carbon\CarbonInterface ? $c->format('H:i:s') : (string) $c;
                                    if ($cs < $os) {
                                        return __('filament-opening-times::opening_schedule.overnight_hint');
                                    }

                                    return null;
                                }),
                        ])
                        ->columns(2)
                        ->addActionLabel(__('filament-opening-times::opening_schedule.add_interval'))
                        ->visible(fn (Get $get): bool => ! $get("day_{$d}_closed"))
                        ->defaultItems(0),
                ])
                ->columns(1)
                ->columnSpanFull();
        }

        return $schema->components($components);
    }
}
