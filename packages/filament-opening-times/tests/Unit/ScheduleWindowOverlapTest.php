<?php

namespace BeegoodIT\FilamentOpeningTimes\Tests\Unit;

use BeegoodIT\FilamentOpeningTimes\Support\ScheduleWindowOverlap;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ScheduleWindowOverlapTest extends TestCase
{
    public function test_touching_endpoints_do_not_overlap(): void
    {
        $a = Carbon::parse('2026-01-01 00:00:00');
        $b = Carbon::parse('2026-01-10 23:59:59');
        $c = Carbon::parse('2026-01-10 23:59:59');
        $d = Carbon::parse('2026-02-01 00:00:00');

        $this->assertFalse(ScheduleWindowOverlap::rangesOverlap($a, $b, $c, $d));
    }

    public function test_overlapping_ranges_detected(): void
    {
        $newStart = Carbon::parse('2026-01-05');
        $newEnd = Carbon::parse('2026-01-15');
        $exStart = Carbon::parse('2026-01-10');
        $exEnd = Carbon::parse('2026-01-20');

        $this->assertTrue(ScheduleWindowOverlap::rangesOverlap($newStart, $newEnd, $exStart, $exEnd));
    }
}
