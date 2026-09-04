<?php

declare(strict_types=1);

namespace BeegoodIT\DateTimePicker\Tests\Unit;

use BeegoodIT\DateTimePicker\Enums\DateTimeRangeKind;
use BeegoodIT\DateTimePicker\Support\DateTimeRange;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

final class DateTimeRangeTest extends TestCase
{
    public function test_detects_day_month_quarter_and_year(): void
    {
        $timezone = 'UTC';

        $day = DateTimeRange::fromBounds(
            Carbon::parse('2026-08-15', $timezone),
            Carbon::parse('2026-08-15', $timezone),
            $timezone,
        );
        $this->assertSame(DateTimeRangeKind::Day, $day->kind);

        $month = DateTimeRange::fromBounds(
            Carbon::parse('2026-08-01', $timezone),
            Carbon::parse('2026-08-31', $timezone),
            $timezone,
        );
        $this->assertSame(DateTimeRangeKind::Month, $month->kind);

        $quarter = DateTimeRange::fromBounds(
            Carbon::parse('2026-07-01', $timezone),
            Carbon::parse('2026-09-30', $timezone),
            $timezone,
        );
        $this->assertSame(DateTimeRangeKind::Quarter, $quarter->kind);

        $year = DateTimeRange::fromBounds(
            Carbon::parse('2026-01-01', $timezone),
            Carbon::parse('2026-12-31', $timezone),
            $timezone,
        );
        $this->assertSame(DateTimeRangeKind::Year, $year->kind);
    }
}
