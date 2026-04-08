<?php

namespace BeegoodIT\FilamentOpeningTimes\Tests\Unit;

use BeegoodIT\FilamentOpeningTimes\Actions\EvaluateScheduleForOpenable;
use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use BeegoodIT\FilamentOpeningTimes\Models\Slot;
use BeegoodIT\FilamentOpeningTimes\Tests\Fixtures\TestOpenable;
use BeegoodIT\FilamentOpeningTimes\Tests\TestCase;
use Carbon\Carbon;

class EvaluateScheduleForOpenableTest extends TestCase
{
    public function test_no_schedules_returns_not_active_and_closed(): void
    {
        $openable = TestOpenable::query()->create();

        $result = EvaluateScheduleForOpenable::run($openable, Carbon::parse('2026-04-06 12:00:00', 'UTC'));

        $this->assertFalse($result->hasActiveSchedule);
        $this->assertFalse($result->isOpen);
        $this->assertNull($result->nextTransition);
    }

    public function test_schedule_outside_active_window_is_ignored(): void
    {
        $openable = TestOpenable::query()->create();
        $schedule = Schedule::query()->create([
            'openable_type' => $openable->getMorphClass(),
            'openable_id' => $openable->getKey(),
            'timezone' => 'UTC',
            'active_from' => Carbon::parse('2027-01-01 00:00:00', 'UTC'),
            'active_to' => Carbon::parse('2027-12-31 23:59:59', 'UTC'),
        ]);
        Slot::query()->create([
            'schedule_id' => $schedule->getKey(),
            'day_of_week' => 1,
            'opens_at' => '09:00:00',
            'closes_at' => '17:00:00',
            'sort_order' => 0,
        ]);

        $result = EvaluateScheduleForOpenable::run($openable, Carbon::parse('2026-04-06 12:00:00', 'UTC'));

        $this->assertFalse($result->hasActiveSchedule);
        $this->assertFalse($result->isOpen);
    }

    public function test_active_schedule_open_inside_slot(): void
    {
        $openable = TestOpenable::query()->create();
        $schedule = Schedule::query()->create([
            'openable_type' => $openable->getMorphClass(),
            'openable_id' => $openable->getKey(),
            'timezone' => 'UTC',
            'active_from' => Carbon::parse('2020-01-01 00:00:00', 'UTC'),
            'active_to' => Carbon::parse('2030-12-31 23:59:59', 'UTC'),
        ]);
        Slot::query()->create([
            'schedule_id' => $schedule->getKey(),
            'day_of_week' => 1,
            'opens_at' => '09:00:00',
            'closes_at' => '17:00:00',
            'sort_order' => 0,
        ]);

        $at = Carbon::parse('2026-04-06 12:00:00', 'UTC');

        $result = EvaluateScheduleForOpenable::run($openable, $at);

        $this->assertTrue($result->hasActiveSchedule);
        $this->assertTrue($result->isOpen);
    }

    public function test_active_schedule_closed_outside_slot_hours(): void
    {
        $openable = TestOpenable::query()->create();
        $schedule = Schedule::query()->create([
            'openable_type' => $openable->getMorphClass(),
            'openable_id' => $openable->getKey(),
            'timezone' => 'UTC',
            'active_from' => Carbon::parse('2020-01-01 00:00:00', 'UTC'),
            'active_to' => Carbon::parse('2030-12-31 23:59:59', 'UTC'),
        ]);
        Slot::query()->create([
            'schedule_id' => $schedule->getKey(),
            'day_of_week' => 1,
            'opens_at' => '09:00:00',
            'closes_at' => '17:00:00',
            'sort_order' => 0,
        ]);

        $result = EvaluateScheduleForOpenable::run($openable, Carbon::parse('2026-04-06 18:00:00', 'UTC'));

        $this->assertTrue($result->hasActiveSchedule);
        $this->assertFalse($result->isOpen);
    }
}
