<?php

namespace BeegoodIT\FilamentOpeningTimes\Tests\Unit;

use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use BeegoodIT\FilamentOpeningTimes\Models\Slot;
use BeegoodIT\FilamentOpeningTimes\Support\OpeningHoursEvaluator;
use BeegoodIT\FilamentOpeningTimes\Tests\TestCase;
use Carbon\Carbon;

class OpeningHoursEvaluatorTest extends TestCase
{
    public function test_same_day_open_inside_interval(): void
    {
        $schedule = $this->makeSchedule('UTC', '2020-01-01', '2030-01-01');
        $slot = Slot::make([
            'day_of_week' => 1,
            'opens_at' => '09:00:00',
            'closes_at' => '17:00:00',
        ]);
        $schedule->setRelation('slots', collect([$slot]));

        $at = Carbon::parse('2026-04-06 12:00:00', 'UTC');

        $this->assertTrue(OpeningHoursEvaluator::isOpen($schedule, $at));
    }

    public function test_same_day_closed_outside_interval(): void
    {
        $schedule = $this->makeSchedule('UTC', '2020-01-01', '2030-01-01');
        $slot = Slot::make([
            'day_of_week' => 1,
            'opens_at' => '09:00:00',
            'closes_at' => '17:00:00',
        ]);
        $schedule->setRelation('slots', collect([$slot]));

        $at = Carbon::parse('2026-04-06 18:00:00', 'UTC');

        $this->assertFalse(OpeningHoursEvaluator::isOpen($schedule, $at));
    }

    public function test_overnight_morning_belongs_to_previous_evening_slot(): void
    {
        $schedule = $this->makeSchedule('UTC', '2020-01-01', '2030-01-01');
        $slot = Slot::make([
            'day_of_week' => 0,
            'opens_at' => '22:00:00',
            'closes_at' => '02:00:00',
        ]);
        $schedule->setRelation('slots', collect([$slot]));

        $at = Carbon::parse('2026-04-06 01:00:00', 'UTC');

        $this->assertTrue(OpeningHoursEvaluator::isOpen($schedule, $at));
    }

    private function makeSchedule(string $tz, string $from, string $to): Schedule
    {
        return Schedule::make([
            'timezone' => $tz,
            'active_from' => Carbon::parse($from),
            'active_to' => Carbon::parse($to),
        ]);
    }
}
