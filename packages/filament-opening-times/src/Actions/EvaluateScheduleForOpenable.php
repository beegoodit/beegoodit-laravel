<?php

namespace BeegoodIT\FilamentOpeningTimes\Actions;

use BeegoodIT\FilamentOpeningTimes\DataTransferObjects\ScheduleStatusResult;
use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use BeegoodIT\FilamentOpeningTimes\Support\OpeningHoursEvaluator;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

class EvaluateScheduleForOpenable
{
    use AsAction;

    public function handle(Model $openable, ?CarbonInterface $at = null): ScheduleStatusResult
    {
        $at ??= now();

        $candidates = Schedule::query()
            ->where('openable_type', $openable->getMorphClass())
            ->where('openable_id', $openable->getKey())
            ->get()
            ->filter(fn (Schedule $s): bool => $s->isActiveAt($at))
            ->sortByDesc(fn (Schedule $s): string => (string) $s->getKey())
            ->values();

        if ($candidates->isEmpty()) {
            return new ScheduleStatusResult(
                hasActiveSchedule: false,
                isOpen: false,
                nextTransition: null,
            );
        }

        /** @var Schedule $schedule */
        $schedule = $candidates->first();

        $isOpen = OpeningHoursEvaluator::isOpen($schedule, $at);

        return new ScheduleStatusResult(
            hasActiveSchedule: true,
            isOpen: $isOpen,
            nextTransition: null,
        );
    }
}
