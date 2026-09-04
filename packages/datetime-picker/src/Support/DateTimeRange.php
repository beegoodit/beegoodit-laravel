<?php

declare(strict_types=1);

namespace BeegoodIT\DateTimePicker\Support;

use BeegoodIT\DateTimePicker\Enums\DateTimeRangeKind;
use Carbon\Carbon;

final class DateTimeRange
{
    public function __construct(
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly DateTimeRangeKind $kind,
    ) {}

    public static function fromBounds(Carbon $start, Carbon $end, string $timezone): self
    {
        $start = $start->copy()->timezone($timezone)->startOfDay();
        $end = $end->copy()->timezone($timezone)->startOfDay();

        return new self($start, $end, self::detectKind($start, $end, $timezone));
    }

    public function label(?string $locale = null): string
    {
        return match ($this->kind) {
            DateTimeRangeKind::Day => DateTimeLabel::formatDay($this->start, $locale),
            DateTimeRangeKind::Year => (string) $this->start->year,
            DateTimeRangeKind::Quarter => __('datetime-picker::picker.quarter_period', [
                'quarter' => (int) ceil($this->start->month / 3),
                'year' => $this->start->year,
            ]),
            DateTimeRangeKind::Month => DateTimeLabel::formatMonth($this->start, $locale),
            DateTimeRangeKind::Custom => DateTimeLabel::formatDay($this->start, $locale)
                .' – '
                .DateTimeLabel::formatDay($this->end, $locale),
        };
    }

    private static function detectKind(Carbon $start, Carbon $end, string $timezone): DateTimeRangeKind
    {
        if ($start->isSameDay($end)) {
            return DateTimeRangeKind::Day;
        }

        $yearStart = $start->copy()->startOfYear()->startOfDay();
        $yearEnd = $start->copy()->endOfYear()->startOfDay();
        if ($start->isSameDay($yearStart) && $end->isSameDay($yearEnd)) {
            return DateTimeRangeKind::Year;
        }

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $quarterStart = Carbon::create(
                $start->year,
                (($quarter - 1) * 3) + 1,
                1,
                0,
                0,
                0,
                $timezone,
            )->startOfDay();
            $quarterEnd = $quarterStart->copy()->addMonthsNoOverflow(2)->endOfMonth()->startOfDay();

            if ($start->isSameDay($quarterStart) && $end->isSameDay($quarterEnd)) {
                return DateTimeRangeKind::Quarter;
            }
        }

        $monthStart = $start->copy()->startOfMonth()->startOfDay();
        $monthEnd = $start->copy()->endOfMonth()->startOfDay();
        if ($start->isSameDay($monthStart) && $end->isSameDay($monthEnd)) {
            return DateTimeRangeKind::Month;
        }

        return DateTimeRangeKind::Custom;
    }
}
