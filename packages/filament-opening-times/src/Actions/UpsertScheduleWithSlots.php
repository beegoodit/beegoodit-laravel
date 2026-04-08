<?php

namespace BeegoodIT\FilamentOpeningTimes\Actions;

use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use BeegoodIT\FilamentOpeningTimes\Models\Slot;
use BeegoodIT\FilamentOpeningTimes\Support\ScheduleWindowOverlap;
use BeegoodIT\FilamentOpeningTimes\Support\SlotValidator;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpsertScheduleWithSlots
{
    use AsAction;

    /**
     * @param  array<int, array{day_of_week: int, opens_at: string, closes_at: string}>  $slots
     */
    public function handle(
        ?Schedule $schedule,
        string $openableType,
        string $openableId,
        string $timezone,
        CarbonInterface|string $activeFrom,
        CarbonInterface|string $activeTo,
        array $slots,
    ): Schedule {
        $activeFrom = $activeFrom instanceof CarbonInterface ? $activeFrom : Carbon::parse($activeFrom);
        $activeTo = $activeTo instanceof CarbonInterface ? $activeTo : Carbon::parse($activeTo);

        $normalizedSlots = [];
        foreach ($slots as $i => $row) {
            $normalizedSlots[] = [
                'day_of_week' => (int) $row['day_of_week'],
                'opens_at' => self::normalizeTimeString($row['opens_at']),
                'closes_at' => self::normalizeTimeString($row['closes_at']),
                'sort_order' => $i,
            ];
        }

        SlotValidator::validate($normalizedSlots);

        $conflict = Schedule::query()
            ->where('openable_type', $openableType)
            ->where('openable_id', $openableId)
            ->when($schedule !== null, fn ($q) => $q->whereKeyNot($schedule->getKey()))
            ->get()
            ->first(function (Schedule $other) use ($activeFrom, $activeTo): bool {
                return ScheduleWindowOverlap::rangesOverlap(
                    $activeFrom,
                    $activeTo,
                    $other->active_from,
                    $other->active_to,
                );
            });

        if ($conflict !== null) {
            throw ValidationException::withMessages([
                'active_from' => [__('filament-opening-times::opening_schedule.validation.overlapping_active_window')],
            ]);
        }

        return DB::transaction(function () use ($schedule, $openableType, $openableId, $timezone, $activeFrom, $activeTo, $normalizedSlots): Schedule {
            if ($schedule === null) {
                $schedule = Schedule::query()->create([
                    'openable_type' => $openableType,
                    'openable_id' => $openableId,
                    'timezone' => $timezone,
                    'active_from' => $activeFrom,
                    'active_to' => $activeTo,
                ]);
            } else {
                $schedule->update([
                    'timezone' => $timezone,
                    'active_from' => $activeFrom,
                    'active_to' => $activeTo,
                ]);
            }

            $schedule->slots()->delete();

            foreach ($normalizedSlots as $row) {
                Slot::query()->create([
                    'schedule_id' => $schedule->getKey(),
                    'day_of_week' => $row['day_of_week'],
                    'opens_at' => $row['opens_at'],
                    'closes_at' => $row['closes_at'],
                    'sort_order' => $row['sort_order'],
                ]);
            }

            return $schedule->load('slots');
        });
    }

    private static function normalizeTimeString(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('H:i:s');
        }

        if (is_string($value)) {
            if (strlen($value) === 5) {
                return $value.':00';
            }

            return $value;
        }

        return '00:00:00';
    }
}
